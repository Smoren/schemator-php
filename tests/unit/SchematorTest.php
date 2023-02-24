<?php

namespace Smoren\Schemator\Tests\Unit;

use Codeception\Test\Unit;
use Exception;
use Smoren\Schemator\Components\Schemator;
use Smoren\Schemator\Exceptions\SchematorException;
use Smoren\Schemator\Factories\SchematorBuilder;
use Smoren\Schemator\Factories\SchematorFactory;
use Smoren\Schemator\Filters\BaseFiltersStorage;
use Smoren\Schemator\Interfaces\FilterContextInterface;
use Smoren\Schemator\Structs\ErrorsLevelMask;

class SchematorTest extends Unit
{
    /**
     * @throws SchematorException
     */
    public function testDefaultDelimiter(): void
    {
        $schemator = SchematorFactory::create();

        $input = [
            'id' => 100,
            'name' => 'Novgorod',
            'country' => [
                'id' => 10,
                'name' => 'Russia',
                'friends' => ['Kazakhstan', 'Belarus', 'Armenia'],
                'capitals' => [
                    'msk' => 'Moscow',
                    'spb' => 'St. Petersburg',
                ],
            ],
            'streets' => [
                [
                    'id' => 1000,
                    'name' => 'Tverskaya',
                    'houses' => [1, 5, 9],
                ],
                [
                    'id' => 1002,
                    'name' => 'Leninskiy',
                    'houses' => [22, 35, 49],
                    'unknown' => null,
                ],
                [
                    'id' => 1003,
                    'name' => 'Tarusskaya',
                    'houses' => [11, 12, 15],
                    'unknown' => 'some value',
                ],
            ],
            'msk_path' => 'country.capitals.msk',
        ];

        $data = $schemator->convert($input, [
            'city_id' => 'id',
            'city_name' => 'name',
            'city_street_names' => 'streets.name',
            'city_street_houses' => 'streets.houses',
            'country_id' => 'country.id',
            'country_name' => 'country.name',
            'country_friends' => 'country.friends',
            'country_friend' => 'country.friends',
            'country_first_capital' => 'country.capitals.msk',
            'country_second_capital' => 'country.capitals.spb',
            'unknown' => 'unknown',
            'unknown_another' => 'country.unknown',
            'unknown_array' => 'streets.unknown',
            'raw' => '',
            'country_data.country_id' => 'country.id',
            'country_data.country_name' => 'country.name',
        ]);

        $this->assertSame(100, $data['city_id']);
        $this->assertSame('Novgorod', $data['city_name']);
        $this->assertEquals(['Tverskaya', 'Leninskiy', 'Tarusskaya'], $data['city_street_names']);
        $this->assertEquals([[1, 5, 9], [22, 35, 49], [11, 12, 15]], $data['city_street_houses']);
        $this->assertSame(10, $data['country_id']);
        $this->assertSame('Russia', $data['country_name']);
        $this->assertEquals(['Kazakhstan', 'Belarus', 'Armenia'], $data['country_friends']);
        $this->assertSame('Moscow', $data['country_first_capital']);
        $this->assertSame('St. Petersburg', $data['country_second_capital']);
        $this->assertNull($data['unknown']);
        $this->assertNull($data['unknown_another']);
        $this->assertEquals([null, 'some value'], $data['unknown_array']);
        $this->assertEquals($input, $data['raw']);
        $this->assertEquals([
            'country_id' => 10,
            'country_name' => 'Russia',
        ], $data['country_data']);

        try {
            $schemator->convert($input, [
                'city_street_names' => ['streets.name', ['join', ', ']]
            ]);
            $this->expectError();
        } catch (SchematorException $e) {
            $this->assertSame(SchematorException::FILTER_NOT_FOUND, $e->getCode());
        }

        $schemator->addFilter(
            'implode',
            function (FilterContextInterface $context, string $delimiter) {
                return implode($delimiter, $context->getSource());
            }
        );

        $schemator->addFilter(
            'explode',
            function (FilterContextInterface $context, string $delimiter) {
                return explode($delimiter, $context->getSource());
            }
        );

        $schemator->addFilter(
            'startsWith',
            function (FilterContextInterface $context, string $start) {
                return array_filter($context->getSource(), function (string $candidate) use ($start) {
                    return strpos($candidate, $start) === 0;
                });
            }
        );

        $schemator->addFilter(
            'path',
            function (FilterContextInterface $context) {
                return $context->getSchemator()->getValue($context->getRootSource(), $context->getSource());
            }
        );

        $data = $schemator->convert($input, [
            'city_street_names' => ['streets.name', ['implode', ', ']]
        ]);
        $this->assertSame('Tverskaya, Leninskiy, Tarusskaya', $data['city_street_names']);

        $data = $schemator->convert($input, [
            'city_street_names' => ['streets.name', ['implode', ', '], ['explode', ', ']]
        ]);
        $this->assertEquals(['Tverskaya', 'Leninskiy', 'Tarusskaya'], $data['city_street_names']);

        $data = $schemator->convert($input, [
            'city_street_names' => ['streets.name', ['startsWith', 'T'], ['implode', ', ']]
        ]);
        $this->assertSame('Tverskaya, Tarusskaya', $data['city_street_names']);

        $data = $schemator->convert($input, [
            'msk' => ['msk_path', ['path']]
        ]);
        $this->assertSame('Moscow', $data['msk']);
    }

    /**
     * @throws SchematorException
     */
    public function testSpecificDelimiter(): void
    {
        $schemator = SchematorFactory::createBuilder()->withPathDelimiter('/')->get();

        $input = [
            'id' => 100,
            'name' => 'Novgorod',
            'country' => [
                'id' => 10,
                'name' => 'Russia',
                'friends' => ['Kazakhstan', 'Belarus', 'Armenia'],
                'capitals' => [
                    'msk' => 'Moscow',
                    'spb' => 'St. Petersburg',
                ],
            ],
            'streets' => [
                [
                    'id' => 1000,
                    'name' => 'Tverskaya',
                    'houses' => [1, 5, 9],
                ],
                [
                    'id' => 1002,
                    'name' => 'Leninskiy',
                    'houses' => [22, 35, 49],
                ],
                [
                    'id' => 1003,
                    'name' => 'Tarusskaya',
                    'houses' => [11, 12, 15],
                ],
            ],
            'msk_path' => 'country/capitals/msk',
        ];

        $data = $schemator->convert($input, [
            'city_id' => 'id',
            'city_name' => 'name',
            'city_street_names' => 'streets/name',
            'city_street_houses' => 'streets/houses',
            'country_id' => 'country/id',
            'country_name' => 'country/name',
            'country_friends' => 'country/friends',
            'country_friend' => 'country/friends',
            'country_first_capital' => 'country/capitals/msk',
            'country_second_capital' => 'country/capitals/spb',
            'unknown' => 'unknown',
            'unknown_another' => 'country/unknown',
            'unknown_array' => 'streets/unknown',
            'raw' => '',
            'country_data/country_id' => 'country/id',
            'country_data/country_name' => 'country/name',
        ]);

        $this->assertSame(100, $data['city_id']);
        $this->assertSame('Novgorod', $data['city_name']);
        $this->assertEquals(['Tverskaya', 'Leninskiy', 'Tarusskaya'], $data['city_street_names']);
        $this->assertEquals([[1, 5, 9], [22, 35, 49], [11, 12, 15]], $data['city_street_houses']);
        $this->assertSame(10, $data['country_id']);
        $this->assertSame('Russia', $data['country_name']);
        $this->assertEquals(['Kazakhstan', 'Belarus', 'Armenia'], $data['country_friends']);
        $this->assertSame('Moscow', $data['country_first_capital']);
        $this->assertSame('St. Petersburg', $data['country_second_capital']);
        $this->assertNull($data['unknown']);
        $this->assertNull($data['unknown_another']);
        $this->assertEquals([], $data['unknown_array']);
        $this->assertEquals($input, $data['raw']);
        $this->assertEquals([
            'country_id' => 10,
            'country_name' => 'Russia',
        ], $data['country_data']);

        try {
            $schemator->convert($input, [
                'city_street_names' => ['streets/name', ['join', ', ']]
            ]);
            $this->expectError();
        } catch (SchematorException $e) {
            $this->assertSame(SchematorException::FILTER_NOT_FOUND, $e->getCode());
        }

        $schemator->addFilter(
            'implode',
            function (FilterContextInterface $context, string $delimiter) {
                return implode($delimiter, $context->getSource());
            }
        );

        $schemator->addFilter(
            'explode',
            function (FilterContextInterface $context, string $delimiter) {
                return explode($delimiter, $context->getSource());
            }
        );

        $schemator->addFilter(
            'startsWith',
            function (FilterContextInterface $context, string $start) {
                return array_filter($context->getSource(), function (string $candidate) use ($start) {
                    return strpos($candidate, $start) === 0;
                });
            }
        );

        $schemator->addFilter(
            'path',
            function (FilterContextInterface $context) {
                return $context->getSchemator()->getValue($context->getRootSource(), $context->getSource());
            }
        );

        $data = $schemator->convert($input, [
            'city_street_names' => ['streets/name', ['implode', ', ']]
        ]);
        $this->assertSame('Tverskaya, Leninskiy, Tarusskaya', $data['city_street_names']);

        $data = $schemator->convert($input, [
            'city_street_names' => ['streets/name', ['implode', ', '], ['explode', ', ']]
        ]);
        $this->assertEquals(['Tverskaya', 'Leninskiy', 'Tarusskaya'], $data['city_street_names']);

        $data = $schemator->convert($input, [
            'city_street_names' => ['streets/name', ['startsWith', 'T'], ['implode', ', ']]
        ]);
        $this->assertSame('Tverskaya, Tarusskaya', $data['city_street_names']);

        $data = $schemator->convert($input, [
            'msk' => ['msk_path', ['path']]
        ]);
        $this->assertSame('Moscow', $data['msk']);
    }

    /**
     * @throws SchematorException
     */
    public function testBuilder(): void
    {
        $schemator = SchematorFactory::createBuilder()
            ->withErrorsLevelMask(ErrorsLevelMask::default())
            ->withFilters(new BaseFiltersStorage())
            ->withFilters([
                'startsWith' => function (FilterContextInterface $context, string $start) {
                    return array_filter($context->getSource(), function (string $candidate) use ($start) {
                        return strpos($candidate, $start) === 0;
                    });
                },
            ])
            ->get();

        $input = [
            'id' => 100,
            'name' => 'Novgorod',
            'country' => [
                'id' => 10,
                'name' => 'Russia',
                'friends' => ['Kazakhstan', 'Belarus', 'Armenia'],
                'capitals' => [
                    'msk' => 'Moscow',
                    'spb' => 'St. Petersburg',
                ],
            ],
            'streets' => [
                [
                    'id' => 1000,
                    'name' => 'Tverskaya',
                    'houses' => [1, 5, 9],
                ],
                [
                    'id' => 1002,
                    'name' => 'Leninskiy',
                    'houses' => [22, 35, 49],
                ],
                [
                    'id' => 1003,
                    'name' => 'Tarusskaya',
                    'houses' => [11, 12, 15],
                ],
            ],
            'msk_path' => 'country.capitals.msk',
        ];

        $data = $schemator->convert($input, [
            'city_street_names.first' => ['streets.name', ['implode', ', ']],
            'city_street_names.second' => ['streets.name', ['implode', ', '], ['explode', ', ']],
            'city_street_names.third' => ['streets.name', ['startsWith', 'T'], ['implode', ', ']],
            'city_street_names.sorted' => ['streets.name', ['sort'], ['implode', ', ']],
            'city_street_names.filtered' => ['streets.name', ['filter', function (string $candidate) {
                return strpos($candidate, 'Len') !== false;
            }]],
            'msk' => ['msk_path', ['path']],
            'city_street_houses' => ['streets.houses', ['flatten']],
            'const' => [['const', 'my const']],
        ]);
        $this->assertSame('Tverskaya, Leninskiy, Tarusskaya', $data['city_street_names']['first']);
        $this->assertEquals(['Tverskaya', 'Leninskiy', 'Tarusskaya'], $data['city_street_names']['second']);
        $this->assertSame('Tverskaya, Tarusskaya', $data['city_street_names']['third']);
        $this->assertSame('Leninskiy, Tarusskaya, Tverskaya', $data['city_street_names']['sorted']);
        $this->assertEquals(['Leninskiy'], $data['city_street_names']['filtered']);
        $this->assertSame('Moscow', $data['msk']);
        $this->assertEquals([1, 5, 9, 22, 35, 49, 11, 12, 15], $data['city_street_houses']);
        $this->assertSame('my const', $data['const']);
    }

    /**
     * @throws SchematorException
     */
    public function testReplaceAndFilter()
    {
        $input = [
            'numbers' => [-1, 10, 5, 22, -10, 0, 35, 7, 8, 9, 0],
        ];

        $schemator = SchematorFactory::create();

        $data = $schemator->convert($input, [
            'number_types' => ['numbers', [
                'replace',
                [
                    ['=0', '=', 0],
                    ['>9', '>', 9],
                    ['<0', '<', 0],
                    ['1-8', 'between', 1, 8],
                ]
            ]]
        ]);

        $this->assertEquals([
            '<0', '>9', '1-8', '>9', '<0', '=0', '>9', '1-8', '1-8', 9, '=0',
        ], $data['number_types']);

        $data = $schemator->convert($input, [
            'number_types' => ['numbers', [
                'replace',
                [
                    ['=0', '=', 0],
                    ['>9', '>', 9],
                    ['<0', '<', 0],
                    ['1-8', 'between', 1, 8],
                    ['another', 'else'],
                ]
            ]]
        ]);

        $this->assertEquals([
            '<0', '>9', '1-8', '>9', '<0', '=0', '>9', '1-8', '1-8', 'another', '=0',
        ], $data['number_types']);

        $data = $schemator->convert($input, [
            'positive' => [
                'numbers',
                ['filter', [['>', 0]]],
                ['sort'],
            ],
            'negative' => [
                'numbers',
                ['filter', [['<', 0]]],
                ['sort'],
            ],
            'complicated' => [
                'numbers',
                ['filter', [['>=', 8], ['<', 0]]],
                ['filter', [['<', 22]]],
                ['sort'],
            ],
        ]);

        $this->assertEquals([5, 7, 8, 9, 10, 22, 35], $data['positive']);
        $this->assertEquals([-10, -1], $data['negative']);
        $this->assertEquals([-10, -1, 8, 9, 10], $data['complicated']);
    }

    /**
     * @throws SchematorException
     */
    public function testFormat()
    {
        $schemator = SchematorFactory::create();

        $input = [
            'date' => 1651161688,
        ];

        $schema = [
            'date' => ['date', ['format', function (int $source, string $format) {
                return gmdate($format, $source);
            }, 'Y-m-d']]
        ];

        $output = $schemator->convert($input, $schema);
        $this->assertSame('2022-04-28', $output['date']);

        $schema = [
            'date' => ['date', ['date', 'Y-m-d']]
        ];
        $output = $schemator->convert($input, $schema);
        $this->assertSame('2022-04-28', $output['date']);

        $schema = [
            'date' => ['date', ['date', 'Y-m-d H:i', 3]]
        ];
        $output = $schemator->convert($input, $schema);
        $this->assertSame('2022-04-28 19:01', $output['date']);

        $schema = [
            'date' => ['date', ['date', 'Y-m-d H:i', 0]]
        ];
        $output = $schemator->convert($input, $schema);
        $this->assertSame('2022-04-28 16:01', $output['date']);

        $schema = [
            'date' => ['date', ['date', ['error: in array'], 0]]
        ];

        $schemator = (new SchematorBuilder())
            ->withErrorsLevelMask(
                ErrorsLevelMask::create([
                    SchematorException::BAD_FILTER_CONFIG,
                ])
            )
            ->withFilters(new BaseFiltersStorage())
            ->get();

        try {
            $schemator->convert($input, $schema);
            $this->expectError();
        } catch (SchematorException $e) {
            $this->assertSame(SchematorException::BAD_FILTER_CONFIG, $e->getCode());
            $this->assertSame('date', $e->getData()['filter_name']);
            $this->assertSame($input['date'], $e->getData()['source']);
        }
    }

    /**
     * @throws SchematorException
     */
    public function testFilters()
    {
        $input = [
            'numbers' => [-1, 10, 5, 22, -10, 0, 35, 7, 8, 9, 0],
        ];

        $schemator = SchematorFactory::create();

        $schema = [
            'result' => ['numbers', ['sum']],
        ];
        $output = $schemator->convert($input, $schema);
        $this->assertSame(85, $output['result']);

        $schema = [
            'result' => ['numbers', ['average']],
        ];
        $output = $schemator->convert($input, $schema);
        $this->assertSame(7.73, round($output['result'], 2));

        $input = [
            'id' => 1,
            'name' => 'Product 1',
            'color_variants' => [
                [
                    'color' => 'red',
                    'size_variants' => [
                        ['size' => 'XS', 'price' => 500],
                        ['size' => 'S', 'price' => 1000],
                    ],
                ],
                [
                    'color' => 'green',
                    'size_variants' => [
                        ['size' => 'XS', 'price' => 800],
                        ['size' => 'S', 'price' => 1200],
                    ],
                ],
            ],
        ];
        $schema = [
            'value' => [
                'color_variants.size_variants.price',
                ['average'],
            ],
        ];
        $output = $schemator->convert($input, $schema);
        $this->assertEquals(['value' => 875], $output);
    }

    public function testFilterErrors()
    {
        $schemator = SchematorFactory::createBuilder()
            ->withErrorsLevelMask(ErrorsLevelMask::all())
            ->withFilters(new BaseFiltersStorage())
            ->get();

        $input = ['numbers' => [1, 2, 3, 4, 5]];
        try {
            $schemator->convert($input, [
                'bad' => [
                    'numbers',
                    ['filter', ['not array']],
                    ['sort'],
                ],
            ]);
            $this->expectError();
        } catch (SchematorException $e) {
            $this->assertSame(SchematorException::BAD_FILTER_CONFIG, $e->getCode());
        }

        $input = ['numbers' => [1, 2, 3, 4, 5]];
        try {
            $schemator->convert($input, [
                'bad' => [
                    'numbers',
                    ['filter', 'not array'],
                    ['sort'],
                ],
            ]);
            $this->expectError();
        } catch (SchematorException $e) {
            $this->assertSame(SchematorException::BAD_FILTER_CONFIG, $e->getCode());
        }

        $input = ['numbers' => 123];
        try {
            $schemator->convert($input, [
                'bad' => [
                    'numbers',
                    ['filter', [['not array']]],
                    ['sort'],
                ],
            ]);
            $this->expectError();
        } catch (SchematorException $e) {
            $this->assertSame(SchematorException::BAD_FILTER_SOURCE, $e->getCode());
        }

        $input = ['numbers' => [1, 2, 3, 4, 5]];
        try {
            $schemator->convert($input, [
                'number_types' => ['numbers', [
                    'replace',
                    'not array',
                ]]
            ]);
        } catch (SchematorException $e) {
            $this->assertSame(SchematorException::BAD_FILTER_CONFIG, $e->getCode());
        }

        $input = ['numbers' => [1, 2, 3, 4, 5]];
        try {
            $schemator->convert($input, [
                'number_types' => ['numbers', [
                    'replace',
                    [
                        'not array',
                    ]
                ]]
            ]);
        } catch (SchematorException $e) {
            $this->assertSame(SchematorException::BAD_FILTER_CONFIG, $e->getCode());
        }

        $schemator->addFilter('myfilter', function (FilterContextInterface $context) {
            throw new Exception();
        });
        try {
            $schemator->convert($input, [
                'number_types' => ['numbers', ['myfilter']]
            ]);
        } catch (SchematorException $e) {
            $this->assertSame(SchematorException::FILTER_ERROR, $e->getCode());
        }
    }

    /**
     * @throws SchematorException
     */
    public function testNestedArrays()
    {
        $data = [
            'id' => 1,
            'countries' => [
                [
                    'name' => 'Russia',
                    'cities' => [
                        [
                            'name' => 'Moscow',
                            'streets' => [
                                ['name' => 'Tverskaya'],
                                ['name' => 'Leninskiy'],
                            ]
                        ],
                        [
                            'name' => 'Novgorod',
                            'streets' => [
                                ['name' => 'Lenina'],
                                ['name' => 'Komsomola'],
                            ],
                        ]
                    ]
                ],
                [
                    'name' => 'Belarus',
                    'cities' => [
                        [
                            'name' => 'Minsk',
                            'streets' => [
                                ['name' => 'Moskovskaya'],
                                ['name' => 'Russkaya'],
                            ]
                        ],
                    ]
                ],
            ]
        ];

        $schema = [
            'country_names' => 'countries.name',
            'city_names' => 'countries.cities.name',
            'street_names' => 'countries.cities.streets.name',
        ];

        $schemator = SchematorFactory::create();

        $result = $schemator->convert($data, $schema);

        $this->assertEquals(['Russia', 'Belarus'], $result['country_names']);
        $this->assertEquals(['Moscow', 'Novgorod', 'Minsk'], $result['city_names']);
        $this->assertEquals([
            'Tverskaya', 'Leninskiy', 'Lenina', 'Komsomola', 'Moskovskaya', 'Russkaya',
        ], $result['street_names']);
    }

    public function testGetValue()
    {
        $input = [
            'a' => [
                'b' => [
                    'c' => 1,
                ]
            ]
        ];
        $schemator = SchematorFactory::create();
        $this->assertEquals($input, $schemator->getValue($input, null));
        $this->assertSame(1, $schemator->getValue($input, 'a.b.c'));
        $this->assertEquals(['c' => 1], $schemator->getValue($input, 'a.b'));

        $this->assertNull($schemator->getValue($input, 'a.b.c.d'));

        $schemator = new Schemator('.', ErrorsLevelMask::nothing());

        // unsupported key type
        $this->assertNull($schemator->getValue($input, (object)[]));
        // unsupported source type
        $this->assertNull($schemator->getValue(null, 'a.b.c'));
        $this->assertNull($schemator->getValue('my string', 'a.b.c'));
        // unsupported filter config type
        $this->assertNull($schemator->getValue($input, ['a', (object)[]]));

        $schemator = SchematorFactory::createBuilder()
            ->withErrorsLevelMask(
                ErrorsLevelMask::create([
                    SchematorException::CANNOT_GET_VALUE,
                    SchematorException::UNSUPPORTED_SOURCE_TYPE,
                    SchematorException::UNSUPPORTED_KEY_TYPE,
                    SchematorException::UNSUPPORTED_FILTER_CONFIG_TYPE,
                ])
            )
            ->get();

        try {
            $this->assertNull($schemator->getValue($input, 'a.b.c.d'));
            $this->expectError();
        } catch (SchematorException $e) {
            $this->assertSame(SchematorException::CANNOT_GET_VALUE, $e->getCode());
            $this->assertSame('a.b.c.d', $e->getData()['key']);
        }
        try {
            $schemator->getValue(null, 'a.b.c');
            $this->expectError();
        } catch (SchematorException $e) {
            $this->assertSame(SchematorException::UNSUPPORTED_SOURCE_TYPE, $e->getCode());
            $this->assertSame('NULL', $e->getData()['source_type']);
        }
        try {
            $schemator->getValue('my string', 'a.b.c');
            $this->expectError();
        } catch (SchematorException $e) {
            $this->assertSame(SchematorException::UNSUPPORTED_SOURCE_TYPE, $e->getCode());
            $this->assertSame('string', $e->getData()['source_type']);
        }

        try {
            $schemator->getValue($input, (object)[]);
            $this->expectError();
        } catch (SchematorException $e) {
            $this->assertSame(SchematorException::UNSUPPORTED_KEY_TYPE, $e->getCode());
            $this->assertSame('object', $e->getData()['key_type']);
        }

        try {
            $schemator->getValue($input, ['a', (object)[]]);
            $this->expectError();
        } catch (SchematorException $e) {
            $this->assertSame(SchematorException::UNSUPPORTED_FILTER_CONFIG_TYPE, $e->getCode());
            $this->assertSame('object', $e->getData()['filter_config_type']);
        }
    }

    public function testGetValueWithFilters()
    {
        $input = [
            'mynull' => null,
            'mydate' => 1660089600,
            'mylist' => [1, 2, 3],
            'mystring' => '1; 2; 3',
            'mysource' => [
                'key' => 123,
            ],
            'mypath' => 'mysource.key',
        ];
        $schemator = SchematorFactory::create();

        $this->assertSame('2022-08-10', $schemator->getValue($input, ['mydate', ['date', 'Y-m-d', 0]]));
        $this->assertNull($schemator->getValue($input, ['mynull', ['date', 'Y-m-d', 0]]));

        $this->assertSame('1; 2; 3', $schemator->getValue($input, ['mylist', ['implode', '; ']]));
        $this->assertNull($schemator->getValue($input, ['mynull', ['implode', '; ']]));

        $this->assertEquals([1, 2, 3], $schemator->getValue($input, ['mystring', ['explode', '; ']]));
        $this->assertNull($schemator->getValue($input, ['mynull', ['explode', '; ']]));

        $this->assertSame(6, $schemator->getValue($input, ['mylist', ['sum']]));
        $this->assertNull($schemator->getValue($input, ['mynull', ['sum']]));

        $this->assertSame(2, $schemator->getValue($input, ['mylist', ['average']]));
        $this->assertNull($schemator->getValue($input, ['mynull', ['average']]));

        $this->assertEquals([1, 2], $schemator->getValue($input, ['mylist', ['filter', [['<', 3]]]]));
        $this->assertNull($schemator->getValue($input, ['mynull', ['filter', [['<', 3]]]]));

        $this->assertEquals([1, 3], $schemator->getValue($input, ['mylist', ['filter', [['!=', 2]]]]));
        $this->assertEquals([2], $schemator->getValue($input, ['mylist', ['filter', [['between strict', 1, 3]]]]));
        $this->assertEquals([1, 3], $schemator->getValue($input, ['mylist', ['filter', [['in', [1, 3]]]]]));
        $this->assertEquals([2], $schemator->getValue($input, ['mylist', ['filter', [['not in', [1, 3]]]]]));

        $this->assertEquals([1, 2, 3], $schemator->getValue($input, ['mylist', ['sort']]));
        $this->assertNull($schemator->getValue($input, ['mynull', ['sort']]));
        $this->assertEquals([3, 2, 1], $schemator->getValue($input, ['mylist', ['sort', function ($lhs, $rhs) {
            return $rhs - $lhs;
        }]]));

        $this->assertEquals([3, 2, 1], $schemator->getValue($input, ['mylist', ['rsort']]));
        $this->assertNull($schemator->getValue($input, ['mynull', ['rsort']]));

        $this->assertSame(123, $schemator->getValue($input, ['mypath', ['path']]));
        $this->assertNull($schemator->getValue($input, ['mynull', ['path']]));

        $this->assertEquals([123], $schemator->getValue($input, ['mysource', ['flatten']]));
        $this->assertNull($schemator->getValue($input, ['mynull', ['flatten']]));
        $this->assertNull($schemator->getValue($input, ['mystring', ['flatten']]));

        $this->assertEquals([0, 0, 3], $schemator->getValue($input, ['mylist', ['replace', [[0, '<=', 2]]]]));
        $this->assertSame(0, $schemator->getValue($input, ['mysource.key', ['replace', [[0, '<=', 200]]]]));
        $this->assertSame(123, $schemator->getValue($input, ['mysource.key', ['replace', [[0, '<=', 122]]]]));
        $this->assertNull($schemator->getValue($input, ['mynull', ['replace', [[0, '<=', 122]]]]));
    }

    public function testErrorsLevel()
    {
        $schemator = SchematorFactory::create();

        {
            $schemator->setErrorsLevelMask(ErrorsLevelMask::create([
                SchematorException::FILTER_NOT_FOUND,
            ]));

            $input = ['date' => 1651161688];
            $schema = ['date' => ['date', ['unknown_filter']]];
            $this->assertFailure($schemator, $input, $schema, SchematorException::FILTER_NOT_FOUND);

            $input = ['date' => 1651161688];
            $schema = ['date' => ['date', ['date', 'Y-m-d H:i', 0]]];
            $this->assertSuccess($schemator, $input, $schema, ['date' => '2022-04-28 16:01']);

            $input = ['date' => 1651161688];
            $schema = ['date' => 'unknown_key'];
            $this->assertSuccess($schemator, $input, $schema, ['date' => null]);
        }

        {
            $schemator->setErrorsLevelMask(ErrorsLevelMask::create([
                SchematorException::BAD_FILTER_CONFIG,
            ]));

            $input = ['date' => 1651161688];
            $schema = ['date' => ['date', ['date', ['error: in array'], 0]]];
            $this->assertFailure($schemator, $input, $schema, SchematorException::BAD_FILTER_CONFIG);

            $input = ['date' => 1651161688];
            $schema = ['date' => ['date', ['date', 'Y-m-d H:i', 0]]];
            $this->assertSuccess($schemator, $input, $schema, ['date' => '2022-04-28 16:01']);

            $input = ['date' => 1651161688];
            $schema = ['date' => 'unknown_key'];
            $this->assertSuccess($schemator, $input, $schema, ['date' => null]);
        }

        {
            $schemator->setErrorsLevelMask(ErrorsLevelMask::create([
                SchematorException::CANNOT_GET_VALUE,
            ]));

            $input = ['key' => 1];
            $schema = ['my_key' => 'unknown_key'];
            $this->assertFailure($schemator, $input, $schema, SchematorException::CANNOT_GET_VALUE);

            $input = ['path_key' => 'unknown.path'];
            $schema = ['path_value' => ['path_key', ['path']]];
            $this->assertFailure($schemator, $input, $schema, SchematorException::CANNOT_GET_VALUE);

            $input = ['path_key' => 'known.path', 'known' => ['path' => 1]];
            $schema = ['path_value' => ['path_key', ['path']]];
            $this->assertSuccess($schemator, $input, $schema, ['path_value' => 1]);

            $input = ['key' => 1];
            $schema = ['my_key' => 'key'];
            $this->assertSuccess($schemator, $input, $schema, ['my_key' => 1]);

            $input = ['date' => 1651161688];
            $schema = ['date' => ['date', ['date', ['error: in array'], 0]]];
            $this->assertSuccess($schemator, $input, $schema, ['date' => null]);

            $input = ['date' => 1651161688];
            $schema = ['date' => ['date', ['date', 'Y-m-d H:i', 0]]];
            $this->assertSuccess($schemator, $input, $schema, ['date' => '2022-04-28 16:01']);
        }

        {
            $schemator->setErrorsLevelMask(ErrorsLevelMask::create([
                SchematorException::UNSUPPORTED_SOURCE_TYPE,
            ]));

            $input = null;
            $schema = ['my_key' => 'key'];
            $this->assertFailure($schemator, $input, $schema, SchematorException::UNSUPPORTED_SOURCE_TYPE);

            $input = ['key' => 1];
            $schema = ['my_key' => 'key'];
            $this->assertSuccess($schemator, $input, $schema, ['my_key' => 1]);
        }

        {
            $schemator->setErrorsLevelMask(ErrorsLevelMask::create([
                SchematorException::UNSUPPORTED_KEY_TYPE,
            ]));

            $input = ['key' => 1];
            $schema = ['my_key' => (object)[]];
            $this->assertFailure($schemator, $input, $schema, SchematorException::UNSUPPORTED_KEY_TYPE);

            $input = null;
            $schema = ['my_key' => 'key', 'another' => 'key1'];
            $this->assertSuccess($schemator, $input, $schema, ['my_key' => null, 'another' => null]);

            $input = ['key' => 1];
            $schema = ['my_key' => 'key'];
            $this->assertSuccess($schemator, $input, $schema, ['my_key' => 1]);
        }

        {
            $schemator->setErrorsLevelMask(ErrorsLevelMask::create([
                SchematorException::UNSUPPORTED_FILTER_CONFIG_TYPE,
            ]));

            $input = ['key' => 1];
            $schema = ['my_key' => ['key', (object)[]]];
            $this->assertFailure($schemator, $input, $schema, SchematorException::UNSUPPORTED_FILTER_CONFIG_TYPE);

            $input = null;
            $schema = ['my_key' => 'key', 'another' => 'key1'];
            $this->assertSuccess($schemator, $input, $schema, ['my_key' => null, 'another' => null]);

            $input = ['key' => 1];
            $schema = ['my_key' => 'key'];
            $this->assertSuccess($schemator, $input, $schema, ['my_key' => 1]);
        }

        {
            $schemator->setErrorsLevelMask(ErrorsLevelMask::all());

            $input = ['date' => 1651161688];
            $schema = ['date' => ['date', ['unknown_filter']]];
            $this->assertFailure($schemator, $input, $schema, SchematorException::FILTER_NOT_FOUND);

            $input = ['date' => 1651161688];
            $schema = ['date' => ['date', ['date', ['error: in array'], 0]]];
            $this->assertFailure($schemator, $input, $schema, SchematorException::BAD_FILTER_CONFIG);

            $input = ['key' => 1];
            $schema = ['my_key' => 'unknown_key'];
            $this->assertFailure($schemator, $input, $schema, SchematorException::CANNOT_GET_VALUE);

            $input = null;
            $schema = ['my_key' => 'key'];
            $this->assertFailure($schemator, $input, $schema, SchematorException::UNSUPPORTED_SOURCE_TYPE);

            $input = ['key' => 1];
            $schema = ['my_key' => (object)[]];
            $this->assertFailure($schemator, $input, $schema, SchematorException::UNSUPPORTED_KEY_TYPE);

            $input = ['key' => 1];
            $schema = ['my_key' => ['key', (object)[]]];
            $this->assertFailure($schemator, $input, $schema, SchematorException::UNSUPPORTED_FILTER_CONFIG_TYPE);
        }

        {
            $schemator->setErrorsLevelMask(
                ErrorsLevelMask::default()
                    ->add([SchematorException::CANNOT_GET_VALUE])
            );

            $input = ['date' => 1651161688];
            $schema = ['date' => ['date', ['unknown_filter']]];
            $this->assertFailure($schemator, $input, $schema, SchematorException::FILTER_NOT_FOUND);

            $input = ['date' => 1651161688];
            $schema = ['date' => ['date', ['date', ['error: in array'], 0]]];
            $this->assertFailure($schemator, $input, $schema, SchematorException::BAD_FILTER_CONFIG);

            $input = ['key' => 1];
            $schema = ['my_key' => 'unknown_key'];
            $this->assertFailure($schemator, $input, $schema, SchematorException::CANNOT_GET_VALUE);

            $input = null;
            $schema = ['my_key' => 'key'];
            $this->assertFailure($schemator, $input, $schema, SchematorException::UNSUPPORTED_SOURCE_TYPE);

            $input = ['key' => 1];
            $schema = ['my_key' => (object)[]];
            $this->assertFailure($schemator, $input, $schema, SchematorException::UNSUPPORTED_KEY_TYPE);

            $input = ['key' => 1];
            $schema = ['my_key' => ['key', (object)[]]];
            $this->assertFailure($schemator, $input, $schema, SchematorException::UNSUPPORTED_FILTER_CONFIG_TYPE);
        }

        {
            $schemator->setErrorsLevelMask(
                ErrorsLevelMask::default()
                    ->add([SchematorException::CANNOT_GET_VALUE])
                    ->sub([
                        SchematorException::FILTER_NOT_FOUND,
                        SchematorException::UNSUPPORTED_SOURCE_TYPE,
                        SchematorException::BAD_FILTER_CONFIG,
                    ])
            );

            $input = ['date' => 1651161688];
            $schema = ['date' => ['date', ['unknown_filter']]];
            $this->assertSuccess($schemator, $input, $schema, ['date' => null]);

            $input = ['date' => 1651161688];
            $schema = ['date' => ['date', ['date', ['error: in array'], 0]]];
            $this->assertSuccess($schemator, $input, $schema, ['date' => null]);

            $input = ['key' => 1];
            $schema = ['my_key' => 'unknown_key'];
            $this->assertFailure($schemator, $input, $schema, SchematorException::CANNOT_GET_VALUE);

            $input = null;
            $schema = ['my_key' => 'key'];
            $this->assertSuccess($schemator, $input, $schema, ['my_key' => null]);

            $input = ['key' => 1];
            $schema = ['my_key' => (object)[]];
            $this->assertFailure($schemator, $input, $schema, SchematorException::UNSUPPORTED_KEY_TYPE);

            $input = ['key' => 1];
            $schema = ['my_key' => ['key', (object)[]]];
            $this->assertFailure($schemator, $input, $schema, SchematorException::UNSUPPORTED_FILTER_CONFIG_TYPE);
        }

        {
            $schemator->setErrorsLevelMask(ErrorsLevelMask::nothing());

            $input = ['path_key' => 'unknown.path'];
            $schema = ['path_value' => ['path_key', ['path']]];
            $this->assertSuccess($schemator, $input, $schema, ['path_value' => null]);
        }
    }

    protected function assertSuccess(Schemator $schemator, $input, $schema, $value)
    {
        try {
            $this->assertSame($value, $schemator->convert($input, $schema));
        } catch (SchematorException $e) {
            $this->expectError();
        }
    }

    protected function assertFailure(Schemator $schemator, $input, $schema, int $errorCode)
    {
        try {
            $schemator->convert($input, $schema);
            $this->expectError();
        } catch (SchematorException $e) {
            $this->assertSame($errorCode, $e->getCode());
        }
    }
}
