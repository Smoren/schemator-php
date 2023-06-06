<?php

namespace Smoren\Schemator\Tests\Unit\NestedAccessor;

use Smoren\Schemator\Components\NestedAccessor;

class NestedAccessorGetTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForGetStrictSuccess
     * @dataProvider dataProviderForGetStrictSuccessCitiesExample
     */
    public function testGetStrictSuccess($source, $path, $expected)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // When
        $actual = $accessor->get($path);

        // Then
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider dataProviderForGetStrictSuccess
     * @dataProvider dataProviderForGetStrictSuccessCitiesExample
     * @dataProvider dataProviderForGetNonStrictSuccessCitiesExample
     */
    public function testGetNonStrictSuccess($source, $path, $expected)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // When
        $actual = $accessor->get($path, false);

        // Then
        $this->assertEquals($expected, $actual);
    }

    public function dataProviderForGetStrictSuccess(): array
    {
        return [
            [
                [],
                [],
                [],
            ],
            [
                [],
                null,
                [],
            ],
            [
                [],
                '*',
                [],
            ],
            [
                ['a' => 1],
                [],
                ['a' => 1],
            ],
            [
                [1, 2, 3],
                null,
                [1, 2, 3],
            ],
            [
                [1, 2, 3],
                '*',
                [1, 2, 3],
            ],
            [
                [1, 2, 3, 'a' => 4],
                '*',
                [1, 2, 3, 4],
            ],
            [
                [1, 2, 3, 'a' => 4],
                0,
                1,
            ],
            [
                [1, 2, 3, 'a' => 4],
                '0',
                1,
            ],
            [
                [1, 2, 3, 'a' => 4],
                '2',
                3,
            ],
            [
                [1, 2, 3, 'a' => 4],
                2,
                3,
            ],
            [
                [1, 2, 3, 'a' => 4],
                'a',
                4,
            ],
            [
                [1, 2, 3, 'a' => [1, 2, 'a' => 3]],
                'a',
                [1, 2, 'a' => 3],
            ],
            [
                [1, 2, 3, 'a' => [1, 2, 'a' => 3]],
                'a.*',
                [1, 2, 3],
            ],
            [
                [1, 2, 3, 'a' => ['b' => 11, 'c' => 22]],
                'a.*',
                [11, 22],
            ],
            [
                [1, 2, 3, 'a' => ['b' => [11], 'c' => [22]]],
                'a.*',
                [[11], [22]],
            ],
            [
                [1, 2, 3, 'a' => ['b' => [11], 'c' => [22]]],
                'a.*.0',
                [11, 22],
            ],
            [
                [1, 2, 3, 'a' => ['b' => [11], 'c' => [22]]],
                'a.*.*',
                [11, 22],
            ],
            [
                [1, 2, 3, 'a' => ['b' => [11, 22], 'c' => [33, 44]]],
                'a.*.*',
                [11, 22, 33, 44],
            ],
            [
                [1, 2, 3, 'a' => ['b' => [[11, 22]], 'c' => [[33, 44]]]],
                'a.*.0.0',
                [11, 22],
            ],
            [
                [1, 2, 3, 'a' => ['b' => [[11, 22]], 'c' => [[33, 44]]]],
                'a.*.0.*.*',
                [11, 22, 33, 44],
            ],
            [
                [1, 2, 3, 'a' => ['b' => [[11, 22]], 'c' => [[33, 44]]]],
                'a.*.0.*.1',
                [22, 44],
            ],
            [
                [1, 2, 3, 'a' => [1, 2, 'b' => ['c', 'd', 'e']]],
                'a.b',
                ['c', 'd', 'e'],
            ],
            [
                [1, 2, 3, 'a' => [1, 2, 'b' => ['c', 'd', 'e']]],
                'a.b.*',
                ['c', 'd', 'e'],
            ],
            [
                [1, 2, 3, 'a' => ['b' => ['c', 'd', 'e'], [11, 22]]],
                'a.*.*',
                ['c', 'd', 'e', 11, 22],
            ],
            [
                [1, 2, 3, 'a' => ['b' => [['c'], ['d'], ['e']], [[11], [22, 33]]]],
                'a.*.*.*',
                ['c', 'd', 'e', 11, 22, 33],
            ],
            [
                [1, 2, 3, 'a' => ['b' => [['c'], ['d'], ['e']], [[11], [22, 33]]]],
                'a.*.*.0',
                ['c', 'd', 'e', 11, 22],
            ],
            [
                [1, 2, 3, 'a' => ['b' => ['c', 'd', 'e'], [11, 22]]],
                ['a', '*', '*'],
                ['c', 'd', 'e', 11, 22],
            ],
            [
                [
                    'a' => [1, 2, 3],
                    'b' => [11, 22, 33],
                    'c' => [111, 222, 333],
                ],
                '*.0',
                [1, 11, 111],
            ],
            [
                [
                    'a' => [1, 2, 3],
                    'b' => [11, 22, 33],
                    'c' => [111, 222, 333],
                ],
                ['*', 0],
                [1, 11, 111],
            ],
            [
                [
                    'a' => [1, 2, 3],
                    'b' => [11, 22, 33],
                    'c' => [111, 222, 333],
                ],
                ['*', '2'],
                [3, 33, 333],
            ],
            [
                [
                    'a' => [1, 2, [3]],
                    'b' => [11, 22, [33]],
                    'c' => [111, 222, [333]],
                ],
                ['*', '2'],
                [[3], [33], [333]],
            ],
            [
                [
                    'a' => [1, 2, [3]],
                    'b' => [11, 22, [33]],
                    'c' => [111, 222, [333]],
                ],
                ['*', '2', '*', '*'],
                [3, 33, 333],
            ],
            [
                [
                    'a' => [1, 2, 3],
                    'b' => [11, 22, 33],
                    'c' => [111, 222, 333],
                ],
                '*.*',
                [1, 2, 3, 11, 22, 33, 111, 222, 333],
            ],
            [
                [
                    [
                        'a' => [1, 2, 3],
                        'b' => [11, 22, 33],
                        'c' => [111, 222, 333],
                    ],
                ],
                '*.*.0',
                [1, 11, 111],
            ],
            [
                [
                    [
                        [
                            'a' => [1, 2, 3],
                            'b' => [11, 22, 33],
                            'c' => [111, 222, 333],
                        ],
                    ],
                    [
                        [
                            'a' => [4, 5],
                            'b' => [44, 55],
                            'c' => [444, 555],
                        ],
                    ],
                ],
                '*.*.*.0',
                [1, 11, 111, 4, 44, 444],
            ],
            [
                [
                    [
                        [
                            'a' => [1, 2, 3],
                            'b' => [11, 22, 33],
                            'c' => [111, 222, 333],
                        ],
                    ],
                    [
                        [
                            'a' => [4, 5],
                            'b' => [44, 55],
                            'c' => [444, 555],
                        ],
                    ],
                ],
                '*.*.a',
                [[1, 2, 3], [4, 5]],
            ],
            [
                [
                    [
                        [
                            'a' => [1, 2, 3],
                            'b' => [11, 22, 33],
                            'c' => [111, 222, 333],
                        ],
                    ],
                    [
                        [
                            'a' => [4, 5],
                            'b' => [44, 55],
                            'c' => [444, 555],
                        ],
                    ],
                ],
                '*.*.a.0',
                [1, 2, 3],
            ],
            [
                [
                    [
                        [
                            'a' => [1, 2, 3],
                            'b' => [11, 22, 33],
                            'c' => [111, 222, 333],
                        ],
                    ],
                    [
                        [
                            'a' => [4, 5],
                            'b' => [44, 55],
                            'c' => [444, 555],
                        ],
                    ],
                ],
                '*.*.a.*.1',
                [2, 5],
            ],
            [
                [
                    [
                        [
                            'a' => [1, 2, 3],
                            'b' => [11, 22, 33],
                            'c' => [111, 222, 333],
                        ],
                    ],
                    [
                        [
                            'a' => [4, 5],
                            'b' => [44, 55],
                            'c' => [444, 555],
                        ],
                    ],
                ],
                '*.*.b.|.1',
                [44, 55],
            ],
            [
                [
                    'first' => [
                        [
                            'a' => [1, 2, 3],
                            'b' => [11, 22, 33],
                            'c' => [111, 222, 333],
                        ],
                    ],
                    'second' => [
                        [
                            'a' => [4, 5],
                            'b' => [44, 55],
                        ],
                    ],
                ],
                '*.*.*.1',
                [2, 22, 222, 5, 55],
            ],
            [
                [
                    'first' => [
                        [
                            [
                                'a' => [],
                                'b' => ['aaa'],
                                'c' => ['bbb'],
                            ],
                        ],
                    ],
                    'second' => [
                        [
                            [
                                [1, 2, 3],
                                [11, 22, 33],
                                [111, 222, 333],
                            ],
                            [
                                [1111],
                                [11111],
                            ],
                        ],
                        [
                            [
                                [111111],
                                [1111111],
                            ],
                        ],
                    ],
                ],
                'second.*.*.*.0',
                [1, 11, 111, 1111, 11111, 111111, 1111111],
            ],
            [
                [
                    'first' => [
                        [
                            [
                                'a' => [],
                                'b' => ['aaa'],
                                'c' => ['bbb'],
                            ],
                        ],
                    ],
                    'second' => [
                        [
                            [
                                [1, 2, 3],
                                [11, 22, 33],
                                [111, 222, 333],
                            ],
                            [
                                [1111],
                                [11111],
                            ],
                        ],
                        [
                            [
                                [111111],
                                [1111111],
                            ],
                        ],
                    ],
                ],
                'second.*.*.0.*.0',
                [1, 1111, 111111],
            ],
            [
                [
                    'first' => [
                        [
                            [
                                'a' => [],
                                'b' => ['aaa'],
                                'c' => ['bbb'],
                            ],
                        ],
                    ],
                    'second' => [
                        [
                            [
                                [1, 2, 3],
                                [11, 22, 33],
                                [111, 222, 333],
                            ],
                            [
                                [1111],
                                [11111],
                            ],
                        ],
                        [
                            [
                                [111111],
                                [1111111],
                            ],
                        ],
                    ],
                ],
                'second.*.*.0.*.*',
                [1, 2, 3, 1111, 111111],
            ],
            [
                [
                    'first' => [
                        [
                            [
                                'a' => [],
                                'b' => ['aaa'],
                                'c' => ['bbb'],
                            ],
                        ],
                    ],
                    'second' => [
                        [
                            [
                                [1, 2, 3],
                                [11, 22, 33],
                                [111, 222, 333],
                            ],
                            [
                                [1111],
                                [2222],
                            ],
                        ],
                        [
                            [
                                [11111],
                                [222222],
                            ],
                        ],
                    ],
                ],
                'second.0.0.0',
                [1, 2, 3],
            ],
            [
                [
                    'a' => [
                        [
                            'b' => [
                                [
                                    'c' => [
                                        [
                                            'd' => 1,
                                            'e' => [1, 2, 3],
                                        ]
                                    ],
                                    'f' => [
                                        [
                                            'd' => 2,
                                            'e' => [4, 5, 6],
                                        ]
                                    ],
                                ],
                            ],
                            'i' => [
                                [
                                    'j' => [
                                        [
                                            'd' => 3,
                                            'e' => [7, 8, 9],
                                        ]
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
                'a.*****.d',
                [1, 2, 3],
            ],
            [
                [
                    'a' => [
                        [
                            'b' => [
                                [
                                    'c' => [
                                        [
                                            'd' => 1,
                                            'e' => [1, 2, 3],
                                        ]
                                    ],
                                    'f' => [
                                        [
                                            'd' => 2,
                                            'e' => [4, 5, 6],
                                        ]
                                    ],
                                ],
                            ],
                            'i' => [
                                [
                                    'j' => [
                                        [
                                            'd' => 3,
                                            'e' => [7, 8, 9],
                                        ]
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
                'a.*****.e',
                [[1, 2, 3], [4, 5, 6], [7, 8, 9]],
            ],
            [
                [
                    'a' => [
                        [
                            'b' => [
                                [
                                    'c' => [
                                        [
                                            'd' => 1,
                                            'e' => [1, 2, 3],
                                        ]
                                    ],
                                    'f' => [
                                        [
                                            'd' => 2,
                                            'e' => [4, 5, 6],
                                        ]
                                    ],
                                ],
                            ],
                            'i' => [
                                [
                                    'j' => [
                                        [
                                            'd' => 3,
                                            'e' => [7, 8, 9],
                                        ]
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
                'a.*****.|.1.e',
                [4, 5, 6],
            ],
        ];
    }

    public function dataProviderForGetStrictSuccessCitiesExample(): array
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
                '*.country.*.name',
                ['UK', 'Germany', 'Spain'],
            ],
            [
                $cities,
                '*.streets.*.*.name',
                ['Carnaby Street', 'Abbey Road', 'Brick Lane', 'Oderbergerstrasse'],
            ],
            [
                $cities,
                '*.streets.**.name',
                ['Carnaby Street', 'Abbey Road', 'Brick Lane', 'Oderbergerstrasse'],
            ],
            [
                $cities,
                '*.streets.**.houses.**',
                [1, 5, 9, 22, 35, 49, 11, 12, 15, 2, 6, 12],
            ],
            [
                $cities,
                '*.streets.**.houses.*',
                [[1, 5, 9], [22, 35, 49], [11, 12, 15], [2, 6, 12]],
            ],
            [
                $cities,
                '*.streets.**.houses',
                [[1, 5, 9], [22, 35, 49], [11, 12, 15], [2, 6, 12]],
            ],
        ];
    }

    public function dataProviderForGetNonStrictSuccessCitiesExample(): array
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
                '*.country.*.name',
                ['UK', 'Germany', 'Spain'],
            ],
            [
                $cities,
                '*.streets.*.*.name',
                ['Carnaby Street', 'Abbey Road', 'Brick Lane', 'Oderbergerstrasse'],
            ],
            [
                $cities,
                '*.streets.**.name',
                ['Carnaby Street', 'Abbey Road', 'Brick Lane', 'Oderbergerstrasse'],
            ],
            [
                $cities,
                '*.streets.**.houses.**',
                [1, 5, 9, 22, 35, 49, 2, 6, 12],
            ],
            [
                $cities,
                '*.streets.**.houses.*',
                [[1, 5, 9], [22, 35, 49], [2, 6, 12]],
            ],
            [
                $cities,
                '*.streets.**.houses',
                [[1, 5, 9], [22, 35, 49], [2, 6, 12]],
            ],
        ];
    }
}
