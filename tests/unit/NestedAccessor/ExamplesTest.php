<?php

namespace Smoren\Schemator\Tests\Unit\NestedAccessor;

use Smoren\Schemator\Components\NestedAccessor;

class ExamplesTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForGetStrictSingle
     * @dataProvider dataProviderForGetStrictMultipleIndexed
     * @dataProvider dataProviderForGetCitiesStrict
     */
    public function testGetStrict($source, $path, $expected)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // When
        $actual = $accessor->get($path);

        // Then
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider dataProviderForGetStrictSingle
     * @dataProvider dataProviderForGetStrictMultipleIndexed
     * @dataProvider dataProviderForGetCitiesStrict
     * @dataProvider dataProviderForGetCitiesNonStrict
     */
    public function testGetNonStrict($source, $path, $expected)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // When
        $actual = $accessor->get($path, false);

        // Then
        $this->assertEquals($expected, $actual);
    }

    public function dataProviderForGetStrictSingle(): array
    {
        $source = [1, 2, 3, 'a' => ['b' => [[11, 22]], 'c' => [[33, 44]]]];

        return [
            [
                $source,
                'a',
                ['b' => [[11, 22]], 'c' => [[33, 44]]],
            ],
            [
                $source,
                'a.*',
                [[[11, 22]], [[33, 44]]],
            ],
            [
                $source,
                'a.*.0',
                [[11, 22], [33, 44]],
            ],
            [
                $source,
                'a.*.0.0',
                [11, 33],
            ],
            [
                $source,
                'a.*.0.1',
                [22, 44],
            ],
            [
                $source,
                'a.*.0.|.0',
                [11, 22],
            ],
        ];
    }

    public function dataProviderForGetStrictMultipleIndexed(): array
    {
        $source = [
            ['a' => ['b' => [[11, 22]], 'c' => [[33, 44]]]],
            ['a' => ['b' => [[12, 23]], 'd' => [[34, 45]]]],
            ['a' => ['b' => [[13, 24]], 'e' => [[35, 46]]]],
        ];

        return [
            [
                $source,
                '*.a',
                [
                    ['b' => [[11, 22]], 'c' => [[33, 44]]],
                    ['b' => [[12, 23]], 'd' => [[34, 45]]],
                    ['b' => [[13, 24]], 'e' => [[35, 46]]],
                ],
            ],
            [
                $source,
                '*.a.b',
                [
                    [[11, 22]],
                    [[12, 23]],
                    [[13, 24]],
                ],
            ],
            [
                $source,
                '*.a.b.0',
                [
                    [11, 22],
                    [12, 23],
                    [13, 24],
                ],
            ],
            [
                $source,
                '*.a.b.|.0',
                [[11, 22]],
            ],
            [
                $source,
                '*.a.b.*',
                [
                    [11, 22],
                    [12, 23],
                    [13, 24],
                ],
            ],
            [
                $source,
                '*.a.b.*.0',
                [11, 12, 13],
            ],
            [
                $source,
                '*.a.b.*.1',
                [22, 23, 24],
            ],
            [
                $source,
                '*.a.b.*.*',
                [11, 22, 12, 23, 13, 24],
            ],
            [
                $source,
                '*.a.b.*.|.0',
                [11, 22],
            ],
            [
                $source,
                '*.a.b.*.|.1',
                [12, 23],
            ],
            [
                $source,
                '*.a.b.*.|.2',
                [13, 24],
            ],
        ];
    }

    public function dataProviderForGetCitiesStrict(): array
    {
        $cities = [
            [
                'name' => 'London',
                'country' => [
                    'id' => 111,
                    'name' => 'UK',
                ],
                'streets' => [
                    [
                        'id' => 1000,
                        'name' => 'Carnaby Street',
                        'houses' => [1, 5, 9],
                    ],
                    [
                        'id' => 1002,
                        'name' => 'Abbey Road',
                        'houses' => [22, 35, 49],
                    ],
                    [
                        'id' => 1003,
                        'name' => 'Brick Lane',
                        'houses' => [11, 12, 15],
                    ],
                ],
            ],
            [
                'name' => 'Berlin',
                'country' => [
                    'id' => 222,
                    'name' => 'Germany',
                ],
                'streets' => [
                    [
                        'id' => 2000,
                        'name' => 'Oderbergerstrasse',
                        'houses' => [2, 6, 12],
                    ],
                ],
            ],
            [
                'name' => 'Madrid',
                'country' => [
                    'id' => 333,
                    'name' => 'Spain',
                ],
                'streets' => [],
            ],
        ];

        return [
            [
                $cities,
                '*.name',
                ['London', 'Berlin', 'Madrid'],
            ],
            [
                $cities,
                '*.country.name',
                ['UK', 'Germany', 'Spain'],
            ],
            [
                $cities,
                '*.streets.*.name',
                ['Carnaby Street', 'Abbey Road', 'Brick Lane', 'Oderbergerstrasse'],
            ],
            [
                $cities,
                '*.streets.*.houses.*',
                [1, 5, 9, 22, 35, 49, 11, 12, 15, 2, 6, 12],
            ],
            [
                $cities,
                '*.streets.*.houses',
                [[1, 5, 9], [22, 35, 49], [11, 12, 15], [2, 6, 12]],
            ],
        ];
    }

    public function dataProviderForGetCitiesNonStrict(): array
    {
        $cities = [
            [
                'name' => 'London',
                'country' => [
                    'id' => 111,
                    'name' => 'UK',
                ],
                'streets' => [
                    [
                        'id' => 1000,
                        'name' => 'Carnaby Street',
                        'houses' => [1, 5, 9],
                    ],
                    [
                        'id' => 1002,
                        'name' => 'Abbey Road',
                        'houses' => [22, 35, 49],
                    ],
                    [
                        'id' => 1003,
                        'name' => 'Brick Lane',
                    ],
                ],
            ],
            [
                'name' => 'Berlin',
                'country' => [
                    'id' => 222,
                    'name' => 'Germany',
                ],
                'streets' => [
                    [
                        'id' => 2000,
                        'name' => 'Oderbergerstrasse',
                        'houses' => [2, 6, 12],
                    ],
                ],
            ],
            [
                'name' => 'Madrid',
                'country' => [
                    'id' => 333,
                    'name' => 'Spain',
                ],
            ],
        ];

        return [
            [
                $cities,
                '*.name',
                ['London', 'Berlin', 'Madrid'],
            ],
            [
                $cities,
                '*.country.name',
                ['UK', 'Germany', 'Spain'],
            ],
            [
                $cities,
                '*.streets.*.name',
                ['Carnaby Street', 'Abbey Road', 'Brick Lane', 'Oderbergerstrasse'],
            ],
            [
                $cities,
                '*.streets.*.houses.*',
                [1, 5, 9, 22, 35, 49, 2, 6, 12],
            ],
            [
                $cities,
                '*.streets.*.houses',
                [[1, 5, 9], [22, 35, 49], [2, 6, 12]],
            ],
            [
                $cities,
                '*.streets.*.test',
                [],
            ],
            [
                $cities,
                'streets.*.test',
                null,
            ],
            [
                $cities,
                '*.name.*.test',
                [],
            ],
            [
                $cities,
                '0.name.*',
                null,
            ],
        ];
    }
}
