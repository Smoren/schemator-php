<?php

namespace Smoren\Schemator\Tests\Unit;


use Smoren\Schemator\Exceptions\SchematorException;
use Smoren\Schemator\Schemator;
use Smoren\Schemator\SchematorFactory;

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
        $this->assertEquals([null, null, 'some value'], $data['unknown_array']);
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

        $schemator->addFilter('implode', function(Schemator $schemator, array $source, array $rootSource, string $delimiter) {
            return implode($delimiter, $source);
        });

        $schemator->addFilter('explode', function(Schemator $schemator, string $source, array $rootSource, string $delimiter) {
            return explode($delimiter, $source);
        });

        $schemator->addFilter('startsWith', function(Schemator $schemator, array $source, array $rootSource, string $start) {
            return array_filter($source, function(string $candidate) use ($start) {
                return strpos($candidate, $start) === 0;
            });
        });

        $schemator->addFilter('path', function(Schemator $schemator, string $source, array $rootSource) {
            return $schemator->getValue($rootSource, $source);
        });

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
        $this->assertEquals([null, null, null], $data['unknown_array']);
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

        $schemator->addFilter('implode', function(Schemator $schemator, array $source, array $rootSource, string $delimiter) {
            return implode($delimiter, $source);
        });

        $schemator->addFilter('explode', function(Schemator $schemator, string $source, array $rootSource, string $delimiter) {
            return explode($delimiter, $source);
        });

        $schemator->addFilter('startsWith', function(Schemator $schemator, array $source, array $rootSource, string $start) {
            return array_filter($source, function(string $candidate) use ($start) {
                return strpos($candidate, $start) === 0;
            });
        });

        $schemator->addFilter('path', function(Schemator $schemator, string $source, array $rootSource) {
            return $schemator->getValue($rootSource, $source);
        });

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
            'startsWith' => function(Schemator $schemator, array $source, array $rootSource, string $start) {
                return array_filter($source, function(string $candidate) use ($start) {
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
            '<0', '>9', '1-8', '>9', '<0', '=0', '>9', '1-8', '1-8', null, '=0',
        ], $data['number_types']);

        $data = $schemator->exec($input, [
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
            'date' => ['date', ['date', 'Y-m-d H:i']]
        ];
        $output = $schemator->exec($input, $schema);
        $this->assertEquals('2022-04-28 19:01', $output['date']);

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
            $schemator->exec($input, $schema);
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
                ['flatten'],
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
            'city_names' => ['countries.cities.name', ['flatten']],
            'street_names' => ['countries.cities.streets.name', ['flatten']],
        ];

        $schemator = SchematorFactory::create();
        $result = $schemator->exec($data, $schema);

        $this->assertEquals(['Russia', 'Belarus'], $result['country_names']);
        $this->assertEquals(['Moscow', 'Novgorod', 'Minsk'], $result['city_names']);
        $this->assertEquals(['Tverskaya', 'Leninskiy', 'Lenina', 'Komsomola', 'Moskovskaya', 'Russkaya'], $result['street_names']);
    }
}
