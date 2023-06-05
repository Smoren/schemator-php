<?php

namespace Smoren\Schemator\Tests\Unit\NestedAccessor;

use Smoren\Schemator\Components\NestedAccessor;

class NestedAccessorGetTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForGetSuccess
     */
    public function testGetSuccess($source, $path, $expected)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // When
        $actual = $accessor->get($path);

        // Then
        $this->assertEquals($expected, $actual);
    }

    public function dataProviderForGetSuccess(): array
    {
        return [
//            [
//                [],
//                [],
//                [],
//            ],
//            [
//                [],
//                null,
//                [],
//            ],
//            [
//                [],
//                '*',
//                [],
//            ],
//            [
//                ['a' => 1],
//                [],
//                ['a' => 1],
//            ],
//            [
//                [1, 2, 3],
//                null,
//                [1, 2, 3],
//            ],
//            [
//                [1, 2, 3],
//                '*',
//                [1, 2, 3],
//            ],
//            [
//                [1, 2, 3, 'a' => 4],
//                '*',
//                [1, 2, 3, 4],
//            ],
//            [
//                [1, 2, 3, 'a' => 4],
//                0,
//                1,
//            ],
//            [
//                [1, 2, 3, 'a' => 4],
//                '0',
//                1,
//            ],
//            [
//                [1, 2, 3, 'a' => 4],
//                '2',
//                3,
//            ],
//            [
//                [1, 2, 3, 'a' => 4],
//                2,
//                3,
//            ],
//            [
//                [1, 2, 3, 'a' => 4],
//                'a',
//                4,
//            ],
//            [
//                [1, 2, 3, 'a' => [1, 2, 'a' => 3]],
//                'a',
//                [1, 2, 'a' => 3],
//            ],
//            [
//                [1, 2, 3, 'a' => [1, 2, 'a' => 3]],
//                'a.*',
//                [1, 2, 3],
//            ],
//            [
//                [1, 2, 3, 'a' => ['b' => 11, 'c' => 22]],
//                'a.*',
//                [11, 22],
//            ],
//            [
//                [1, 2, 3, 'a' => ['b' => [11], 'c' => [22]]],
//                'a.*',
//                [[11], [22]],
//            ],
//            [
//                [1, 2, 3, 'a' => ['b' => [11], 'c' => [22]]],
//                'a.*.0',
//                [11, 22],
//            ],
//            [
//                [1, 2, 3, 'a' => ['b' => [11], 'c' => [22]]],
//                'a.*.*',
//                [11, 22],
//            ],
//            [
//                [1, 2, 3, 'a' => ['b' => [11, 22], 'c' => [33, 44]]],
//                'a.*.*',
//                [11, 22, 33, 44],
//            ],
//            [
//                [1, 2, 3, 'a' => ['b' => [[11, 22]], 'c' => [[33, 44]]]],
//                //'a.*.0.*',
//                'a.*.0.0',
//                [11, 22],
//            ],
            [
                [1, 2, 3, 'a' => ['b' => [[11, 22]], 'c' => [[33, 44]]]],
                'a.*.0.*.*.|.0',
                [11, 22],
            ],
//            [
//                [1, 2, 3, 'a' => [1, 2, 'b' => ['c', 'd', 'e']]],
//                'a.b',
//                ['c', 'd', 'e'],
//            ],
//            [
//                [1, 2, 3, 'a' => [1, 2, 'b' => ['c', 'd', 'e']]],
//                'a.b.*',
//                ['c', 'd', 'e'],
//            ],
//            [
//                [1, 2, 3, 'a' => ['b' => ['c', 'd', 'e'], [11, 22]]],
//                'a.>',
//                ['c', 'd', 'e', 11, 22],
//            ],
//            [
//                [1, 2, 3, 'a' => ['b' => ['c', 'd', 'e'], [11, 22]]],
//                ['a', '>'],
//                ['c', 'd', 'e', 11, 22],
//            ],
//            [
//                [
//                    'a' => [1, 2, 3],
//                    'b' => [11, 22, 33],
//                    'c' => [111, 222, 333],
//                ],
//                '*.0',
//                [1, 11, 111],
//            ],
//            [
//                [
//                    'a' => [1, 2, 3],
//                    'b' => [11, 22, 33],
//                    'c' => [111, 222, 333],
//                ],
//                ['*', 0],
//                [1, 11, 111],
//            ],
//            [
//                [
//                    'a' => [1, 2, 3],
//                    'b' => [11, 22, 33],
//                    'c' => [111, 222, 333],
//                ],
//                ['*', '2'],
//                [3, 33, 333],
//            ],
//            [
//                [
//                    [
//                        'a' => [1, 2, 3],
//                        'b' => [11, 22, 33],
//                        'c' => [111, 222, 333],
//                    ],
//                ],
//                '*.*.0',
//                [1, 11, 111],
//            ],
//            [
//                [
//                    [
//                        [
//                            'a' => [1, 2, 3],
//                            'b' => [11, 22, 33],
//                            'c' => [111, 222, 333],
//                        ],
//                    ],
//                    [
//                        [
//                            'a' => [2, 3],
//                            'b' => [22, 33],
//                            'c' => [222, 333],
//                        ],
//                    ],
//                ],
//                '*.*.*.0',
//                [1, 11, 111, 2, 22, 222],
//            ],
//            [
//                [
//                    'first' => [
//                        [
//                            'a' => [1, 2, 3],
//                            'b' => [11, 22, 33],
//                            'c' => [111, 222, 333],
//                        ],
//                    ],
//                    'second' => [
//                        [
//                            'a' => [2, 3],
//                            'b' => [22, 33],
//                        ],
//                    ],
//                ],
//                '*.*.*.1',
//                [2, 22, 222, 3, 33],
//            ],
//            [
//                [
//                    'first' => [
//                        [
//                            [
//                                'a' => [],
//                                'b' => ['aaa'],
//                                'c' => ['bbb'],
//                            ],
//                        ],
//                    ],
//                    'second' => [
//                        [
//                            [
//                                [1, 2, 3],
//                                [11, 22, 33],
//                                [111, 222, 333],
//                            ],
//                            [
//                                [1111],
//                                [11111],
//                            ],
//                        ],
//                        [
//                            [
//                                [111111],
//                                [1111111],
//                            ],
//                        ],
//                    ],
//                ],
//                'second.*.*.*.0',
//                [1, 11, 111, 1111, 11111, 111111, 1111111],
//            ],
//            [
//                [
//                    'first' => [
//                        [
//                            [
//                                'a' => [],
//                                'b' => ['aaa'],
//                                'c' => ['bbb'],
//                            ],
//                        ],
//                    ],
//                    'second' => [
//                        [
//                            [
//                                [1, 2, 3],
//                                [11, 22, 33],
//                                [111, 222, 333],
//                            ],
//                            [
//                                [1111],
//                                [11111],
//                            ],
//                        ],
//                        [
//                            [
//                                [111111],
//                                [1111111],
//                            ],
//                        ],
//                    ],
//                ],
//                'second.*.0.*.0',
//                [1, 11, 111, 111111, 1111111],
//            ],
//            [
//                [
//                    'first' => [
//                        [
//                            [
//                                'a' => [],
//                                'b' => ['aaa'],
//                                'c' => ['bbb'],
//                            ],
//                        ],
//                    ],
//                    'second' => [
//                        [
//                            [
//                                [1, 2, 3],
//                                [11, 22, 33],
//                                [111, 222, 333],
//                            ],
//                            [
//                                [1111],
//                                [2222],
//                            ],
//                        ],
//                        [
//                            [
//                                [11111],
//                                [222222],
//                            ],
//                        ],
//                    ],
//                ],
//                'second.>.>.0',
//                [1, 2, 3],
//            ],
        ];
    }
}
