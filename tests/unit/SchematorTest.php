<?php

namespace Smoren\Schemator\Tests\Unit;

use Smoren\Schemator\Components\Schemator;
use Smoren\Schemator\Exceptions\SchematorException;
use Smoren\Schemator\Factories\SchematorFactory;
use Smoren\Schemator\Interfaces\FilterContextInterface;

class SchematorTest extends \Codeception\Test\Unit
{
    /**
     * @throws SchematorException
     */
    public function testDefaultDelimiter()
    {
        $schemator = new Schemator();

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

        $data = $schemator->exec($input, [
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

        $this->assertEquals(100, $data['city_id']);
        $this->assertEquals('Novgorod', $data['city_name']);
        $this->assertEquals(['Tverskaya', 'Leninskiy', 'Tarusskaya'], $data['city_street_names']);
        $this->assertEquals([[1, 5, 9], [22, 35, 49], [11, 12, 15]], $data['city_street_houses']);
        $this->assertEquals(10, $data['country_id']);
        $this->assertEquals('Russia', $data['country_name']);
        $this->assertEquals(['Kazakhstan', 'Belarus', 'Armenia'], $data['country_friends']);
        $this->assertEquals('Moscow', $data['country_first_capital']);
        $this->assertEquals('St. Petersburg', $data['country_second_capital']);
        $this->assertEquals(null, $data['unknown']);
        $this->assertEquals(null, $data['unknown_another']);
        $this->assertEquals([null, 'some value'], $data['unknown_array']);
        $this->assertEquals($input, $data['raw']);
        $this->assertEquals([
            'country_id' => 10,
            'country_name' => 'Russia',
        ], $data['country_data']);

        try {
            $schemator->exec($input, [
                'city_street_names' => ['streets.name', ['join', ', ']]
            ]);
            $this->assertTrue(false);
        } catch(SchematorException $e) {
            $this->assertEquals(SchematorException::FILTER_NOT_FOUND, $e->getCode());
        }

        $schemator->addFilter(
            'implode',
            function(FilterContextInterface $context, string $delimiter) {
                return implode($delimiter, $context->getSource());
            }
        );

        $schemator->addFilter(
            'explode',
            function(FilterContextInterface $context, string $delimiter) {
                return explode($delimiter, $context->getSource());
            }
        );

        $schemator->addFilter(
            'startsWith',
            function(FilterContextInterface $context, string $start) {
                return array_filter($context->getSource(), function(string $candidate) use ($start) {
                    return strpos($candidate, $start) === 0;
                });
            }
        );

        $schemator->addFilter(
            'path',
            function(FilterContextInterface $context) {
                return $context->getSchemator()->getValue($context->getRootSource(), $context->getSource());
            }
        );

        $data = $schemator->exec($input, [
            'city_street_names' => ['streets.name', ['implode', ', ']]
        ]);
        $this->assertEquals('Tverskaya, Leninskiy, Tarusskaya', $data['city_street_names']);

        $data = $schemator->exec($input, [
            'city_street_names' => ['streets.name', ['implode', ', '], ['explode', ', ']]
        ]);
        $this->assertEquals(['Tverskaya', 'Leninskiy', 'Tarusskaya'], $data['city_street_names']);

        $data = $schemator->exec($input, [
            'city_street_names' => ['streets.name', ['startsWith', 'T'], ['implode', ', ']]
        ]);
        $this->assertEquals('Tverskaya, Tarusskaya', $data['city_street_names']);

        $data = $schemator->exec($input, [
            'msk' => ['msk_path', ['path']]
        ]);
        $this->assertEquals('Moscow', $data['msk']);
    }

    /**
     * @throws SchematorException
     */
    public function testSpecificDelimiter()
    {
        $schemator = new Schemator('/');

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

        $data = $schemator->exec($input, [
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

        $this->assertEquals(100, $data['city_id']);
        $this->assertEquals('Novgorod', $data['city_name']);
        $this->assertEquals(['Tverskaya', 'Leninskiy', 'Tarusskaya'], $data['city_street_names']);
        $this->assertEquals([[1, 5, 9], [22, 35, 49], [11, 12, 15]], $data['city_street_houses']);
        $this->assertEquals(10, $data['country_id']);
        $this->assertEquals('Russia', $data['country_name']);
        $this->assertEquals(['Kazakhstan', 'Belarus', 'Armenia'], $data['country_friends']);
        $this->assertEquals('Moscow', $data['country_first_capital']);
        $this->assertEquals('St. Petersburg', $data['country_second_capital']);
        $this->assertEquals(null, $data['unknown']);
        $this->assertEquals(null, $data['unknown_another']);
        $this->assertEquals([], $data['unknown_array']);
        $this->assertEquals($input, $data['raw']);
        $this->assertEquals([
            'country_id' => 10,
            'country_name' => 'Russia',
        ], $data['country_data']);

        try {
            $schemator->exec($input, [
                'city_street_names' => ['streets/name', ['join', ', ']]
            ]);
            $this->assertTrue(false);
        } catch(SchematorException $e) {
            $this->assertEquals(SchematorException::FILTER_NOT_FOUND, $e->getCode());
        }

        $schemator->addFilter(
            'implode',
            function(FilterContextInterface $context, string $delimiter) {
                return implode($delimiter, $context->getSource());
            }
        );

        $schemator->addFilter(
            'explode',
            function(FilterContextInterface $context, string $delimiter) {
                return explode($delimiter, $context->getSource());
            }
        );

        $schemator->addFilter(
            'startsWith',
            function(FilterContextInterface $context, string $start) {
                return array_filter($context->getSource(), function(string $candidate) use ($start) {
                    return strpos($candidate, $start) === 0;
                });
            }
        );

        $schemator->addFilter(
            'path',
            function(FilterContextInterface $context) {
                return $context->getSchemator()->getValue($context->getRootSource(), $context->getSource());
            }
        );

        $data = $schemator->exec($input, [
            'city_street_names' => ['streets/name', ['implode', ', ']]
        ]);
        $this->assertEquals('Tverskaya, Leninskiy, Tarusskaya', $data['city_street_names']);

        $data = $schemator->exec($input, [
            'city_street_names' => ['streets/name', ['implode', ', '], ['explode', ', ']]
        ]);
        $this->assertEquals(['Tverskaya', 'Leninskiy', 'Tarusskaya'], $data['city_street_names']);

        $data = $schemator->exec($input, [
            'city_street_names' => ['streets/name', ['startsWith', 'T'], ['implode', ', ']]
        ]);
        $this->assertEquals('Tverskaya, Tarusskaya', $data['city_street_names']);

        $data = $schemator->exec($input, [
            'msk' => ['msk_path', ['path']]
        ]);
        $this->assertEquals('Moscow', $data['msk']);
    }

    /**
     * @throws SchematorException
     */
    public function testFactory()
    {
        $schemator = SchematorFactory::create(true, [
            'startsWith' => function(FilterContextInterface $context, string $start) {
                return array_filter($context->getSource(), function(string $candidate) use ($start) {
                    return strpos($candidate, $start) === 0;
                });
            },
        ]);

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

        $data = $schemator->exec($input, [
            'city_street_names.first' => ['streets.name', ['implode', ', ']],
            'city_street_names.second' => ['streets.name', ['implode', ', '], ['explode', ', ']],
            'city_street_names.third' => ['streets.name', ['startsWith', 'T'], ['implode', ', ']],
            'city_street_names.sorted' => ['streets.name', ['sort'], ['implode', ', ']],
            'city_street_names.filtered' => ['streets.name', ['filter', function(string $candidate) {
                return strpos($candidate, 'Len') !== false;
            }]],
            'msk' => ['msk_path', ['path']],
            'city_street_houses' => ['streets.houses', ['flatten']],
            'const' => [['const', 'my const']],
        ]);
        $this->assertEquals('Tverskaya, Leninskiy, Tarusskaya', $data['city_street_names']['first']);
        $this->assertEquals(['Tverskaya', 'Leninskiy', 'Tarusskaya'], $data['city_street_names']['second']);
        $this->assertEquals('Tverskaya, Tarusskaya', $data['city_street_names']['third']);
        $this->assertEquals('Leninskiy, Tarusskaya, Tverskaya', $data['city_street_names']['sorted']);
        $this->assertEquals(['Leninskiy'], $data['city_street_names']['filtered']);
        $this->assertEquals('Moscow', $data['msk']);
        $this->assertEquals([1, 5, 9, 22, 35, 49, 11, 12, 15], $data['city_street_houses']);
        $this->assertEquals('my const', $data['const']);
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

        $data = $schemator->exec($input, [
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

        $data = $schemator->exec($input, [
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
            'date' => ['date', ['format', function(int $source, string $format) {
                return gmdate($format, $source);
            }, 'Y-m-d']]
        ];

        $output = $schemator->exec($input, $schema);
        $this->assertEquals('2022-04-28', $output['date']);

        $schema = [
            'date' => ['date', ['date', 'Y-m-d']]
        ];
        $output = $schemator->exec($input, $schema);
        $this->assertEquals('2022-04-28', $output['date']);

        $schema = [
            'date' => ['date', ['date', 'Y-m-d H:i', 3]]
        ];
        $output = $schemator->exec($input, $schema);
        $this->assertEquals('2022-04-28 19:01', $output['date']);

        $schema = [
            'date' => ['date', ['date', 'Y-m-d H:i', 0]]
        ];
        $output = $schemator->exec($input, $schema);
        $this->assertEquals('2022-04-28 16:01', $output['date']);

        $schema = [
            'date' => ['date', ['date', ['Y-m-d H:i'], 0]]
        ];
        try {
            $schemator->exec($input, $schema, true);
            $this->assertTrue(false);
        } catch(SchematorException $e) {
            $this->assertEquals(SchematorException::FILTER_ERROR, $e->getCode());
            $this->assertEquals('date', $e->getData()['filter_name']);
            $this->assertEquals($input['date'], $e->getData()['source']);
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
        $output = $schemator->exec($input, $schema);
        $this->assertEquals(85, $output['result']);

        $schema = [
            'result' => ['numbers', ['average']],
        ];
        $output = $schemator->exec($input, $schema);
        $this->assertEquals(7.73, round($output['result'], 2));

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
        $output = $schemator->exec($input, $schema);
        $this->assertEquals(['value' => 875], $output);
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
        $result = $schemator->exec($data, $schema);

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
        $schemator = new Schemator();
        $this->assertEquals($input, $schemator->getValue($input, null));
        $this->assertEquals(1, $schemator->getValue($input, 'a.b.c'));
        $this->assertEquals(['c' => 1], $schemator->getValue($input, 'a.b'));

        $this->assertEquals(null, $schemator->getValue($input, 'a.b.c.d'));
        try {
            $this->assertEquals(null, $schemator->getValue($input, 'a.b.c.d', true));
            $this->assertTrue(false);
        } catch(SchematorException $e) {
            $this->assertEquals(SchematorException::CANNOT_GET_VALUE, $e->getCode());
            $this->assertEquals('a.b.c.d', $e->getData()['key']);
        }

        $this->assertEquals(null, $schemator->getValue(null, 'a.b.c'));
        $this->assertEquals(null, $schemator->getValue('my string', 'a.b.c'));
        try {
            $schemator->getValue(null, 'a.b.c', true);
            $this->assertTrue(false);
        } catch(SchematorException $e) {
            $this->assertEquals(SchematorException::UNSUPPORTED_SOURCE_TYPE, $e->getCode());
            $this->assertEquals('NULL', $e->getData()['source_type']);
        }
        try {
            $schemator->getValue('my string', 'a.b.c', true);
            $this->assertTrue(false);
        } catch(SchematorException $e) {
            $this->assertEquals(SchematorException::UNSUPPORTED_SOURCE_TYPE, $e->getCode());
            $this->assertEquals('string', $e->getData()['source_type']);
        }

        $this->assertEquals(null, $schemator->getValue($input, (object)[]));
        try {
            $schemator->getValue($input, (object)[], true);
            $this->assertTrue(false);
        } catch(SchematorException $e) {
            $this->assertEquals(SchematorException::UNSUPPORTED_KEY_TYPE, $e->getCode());
            $this->assertEquals('object', $e->getData()['key_type']);
        }

        $this->assertEquals(null, $schemator->getValue($input, ['a', (object)[]]));
        try {
            $schemator->getValue($input, ['a', (object)[]], true);
            $this->assertTrue(false);
        } catch(SchematorException $e) {
            $this->assertEquals(SchematorException::UNSUPPORTED_FILTER_CONFIG_TYPE, $e->getCode());
            $this->assertEquals('object', $e->getData()['filter_config_type']);
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
        $this->assertEquals('2022-08-10', $schemator->getValue($input, ['mydate', ['date', 'Y-m-d', 0]]));
        $this->assertEquals(null, $schemator->getValue($input, ['mynull', ['date', 'Y-m-d', 0]]));

        $this->assertEquals('1; 2; 3', $schemator->getValue($input, ['mylist', ['implode', '; ']]));
        $this->assertEquals(null, $schemator->getValue($input, ['mynull', ['implode', '; ']]));

        $this->assertEquals([1, 2, 3], $schemator->getValue($input, ['mystring', ['explode', '; ']]));
        $this->assertEquals(null, $schemator->getValue($input, ['mynull', ['explode', '; ']]));

        $this->assertEquals(6, $schemator->getValue($input, ['mylist', ['sum']]));
        $this->assertEquals(null, $schemator->getValue($input, ['mynull', ['sum']]));

        $this->assertEquals(2, $schemator->getValue($input, ['mylist', ['average']]));
        $this->assertEquals(null, $schemator->getValue($input, ['mynull', ['average']]));

        $this->assertEquals([1, 2], $schemator->getValue($input, ['mylist', ['filter', [['<', 3]]]]));
        $this->assertEquals(null, $schemator->getValue($input, ['mynull', ['filter', [['<', 3]]]]));

        $this->assertEquals([1, 2, 3], $schemator->getValue($input, ['mylist', ['sort']]));
        $this->assertEquals(null, $schemator->getValue($input, ['mynull', ['sort']]));
        $this->assertEquals([3, 2, 1], $schemator->getValue($input, ['mylist', ['sort', function($lhs, $rhs) {
            return $rhs - $lhs;
        }]]));

        $this->assertEquals([3, 2, 1], $schemator->getValue($input, ['mylist', ['rsort']]));
        $this->assertEquals(null, $schemator->getValue($input, ['mynull', ['rsort']]));

        $this->assertEquals(123, $schemator->getValue($input, ['mypath', ['path']]));
        $this->assertEquals(null, $schemator->getValue($input, ['mynull', ['path']]));
        $this->assertEquals(null, $schemator->getValue($input, ['mylist', ['path']]));

        $this->assertEquals([123], $schemator->getValue($input, ['mysource', ['flatten']]));
        $this->assertEquals(null, $schemator->getValue($input, ['mynull', ['flatten']]));
        $this->assertEquals(null, $schemator->getValue($input, ['mystring', ['flatten']]));

        $this->assertEquals([0, 0, 3], $schemator->getValue($input, ['mylist', ['replace', [[0, '<=', 2]]]]));
        $this->assertEquals(0, $schemator->getValue($input, ['mysource.key', ['replace', [[0, '<=', 200]]]]));
        $this->assertEquals(123, $schemator->getValue($input, ['mysource.key', ['replace', [[0, '<=', 122]]]]));
        $this->assertEquals(null, $schemator->getValue($input, ['mynull', ['replace', [[0, '<=', 122]]]]));
    }
}
