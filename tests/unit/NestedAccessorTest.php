<?php

namespace Smoren\Schemator\Tests\Unit;

use Smoren\Schemator\Components\NestedAccessor;
use Smoren\Schemator\Exceptions\NestedAccessorException;

class NestedAccessorTest extends \Codeception\Test\Unit
{
    /**
     * @throws NestedAccessorException
     */
    public function testReadSimple()
    {
        $input = [
            'id' => 100,
            'name' => 'Novgorod',
            'status' => null,
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

        $accessor = new NestedAccessor($input);

        $this->assertEquals('Novgorod', $accessor->get('name'));
        $this->assertEquals('Novgorod', $accessor->get('name', true));
        $this->assertEquals('Novgorod', $accessor->get('name', false));

        $this->assertEquals(null, $accessor->get('name1', false));

        try {
            $accessor->get('name1', true);
            $this->expectError();
        } catch(NestedAccessorException $e) {
            $this->assertEquals(NestedAccessorException::CANNOT_GET_VALUE, $e->getCode());
            $this->assertEquals('name1', $e->getData()['key']);
            $this->assertEquals(1, $e->getData()['count']);
        }

        try {
            $accessor->get('name1');
            $this->expectError();
        } catch(NestedAccessorException $e) {
            $this->assertEquals(NestedAccessorException::CANNOT_GET_VALUE, $e->getCode());
            $this->assertEquals('name1', $e->getData()['key']);
            $this->assertEquals(1, $e->getData()['count']);
        }

        try {
            $accessor->get('name1', true);
            $this->expectError();
        } catch(NestedAccessorException $e) {
            $this->assertEquals(NestedAccessorException::CANNOT_GET_VALUE, $e->getCode());
            $this->assertEquals('name1', $e->getData()['key']);
            $this->assertEquals(1, $e->getData()['count']);
        }

        $this->assertEquals(null, $accessor->get('status'));
        $this->assertEquals(null, $accessor->get('status', true));
        $this->assertEquals(null, $accessor->get('status', false));

        $this->assertEquals('Moscow', $accessor->get('country.capitals.msk'));
        $this->assertEquals(null, $accessor->get('country.capitals.msk1', false));
        try {
            $accessor->get('country.capitals.msk1');
            $this->expectError();
        } catch(NestedAccessorException $e) {
            $this->assertEquals(NestedAccessorException::CANNOT_GET_VALUE, $e->getCode());
            $this->assertEquals('country.capitals.msk1', $e->getData()['key']);
            $this->assertEquals(1, $e->getData()['count']);
        }
    }

    /**
     * @throws NestedAccessorException
     */
    public function testReadWithFlattening()
    {
        $input = [
            'countries' => [
                [
                    'name' => 'Russia',
                    'cities' => [
                        [
                            'name' => 'Moscow',
                            'extra' => [
                                'codes' => [
                                    ['value' => 7495],
                                    ['value' => 7499],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Petersburg',
                            'extra' => [
                                'codes' => [
                                    ['value' => 7812],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Belarus',
                    'cities' => [
                        [
                            'name' => 'Minsk',
                            'extra' => [
                                'codes' => [
                                    ['value' => 375017],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $accessor = new NestedAccessor($input);
        $this->assertEquals(['Russia', 'Belarus'], $accessor->get('countries.name'));
        $this->assertEquals(['Moscow', 'Petersburg', 'Minsk'], $accessor->get('countries.cities.name'));
        $this->assertEquals([7495, 7499, 7812, 375017], $accessor->get('countries.cities.extra.codes.value'));

        $input = [
            'countries' => [
                [
                    'name' => 'Russia',
                    'cities' => [
                        [
                            'name' => 'Moscow',
                            'extra' => [
                                'codes' => [7495, 7499],
                            ],
                        ],
                        [
                            'name' => 'Petersburg',
                            'extra' => [
                                'codes' => [7812],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Belarus',
                    'cities' => [
                        [
                            'name' => 'Minsk',
                            'extra' => [
                                'codes' => [375017],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $accessor = new NestedAccessor($input);
        $this->assertEquals([[7495, 7499], [7812], [375017]], $accessor->get('countries.cities.extra.codes'));

        $input = [
            'countries' => [
                [
                    'name' => 'Russia',
                    'cities' => [
                        [
                            'name' => 'Moscow',
                        ],
                        [
                            'name' => 'Petersburg',
                            'extra' => [
                                'codes' => [
                                    ['value' => 7812],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Belarus',
                ],
                [
                    'name' => 'Kazakhstan',
                    'cities' => [
                        'extra' => [
                            'codes' => [
                                'value' => 123,
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Armenia',
                    'cities' => [
                        'extra' => [
                            'codes' => 999,
                        ],
                    ],
                ],
                [
                    'name' => 'Serbia',
                    'cities' => [
                        'extra' => [
                            'codes' => [],
                        ],
                    ],
                ],
            ],
        ];

        $accessor = new NestedAccessor($input);
        $this->assertEquals([7812, 123], $accessor->get('countries.cities.extra.codes.value', false));

        try {
            $accessor->get('countries.cities.extra.codes.value');
            $this->expectError();
        } catch(NestedAccessorException $e) {
            $this->assertEquals(NestedAccessorException::CANNOT_GET_VALUE, $e->getCode());
            $this->assertEquals('countries.cities.extra.codes.value', $e->getData()['key']);
            $this->assertEquals(3, $e->getData()['count']);
        }
    }

    /**
     * @throws NestedAccessorException
     */
    public function testReadObjects()
    {
        $input = (object)[
            'countries' => [
                [
                    'name' => 'Russia',
                    'cities' => [
                        (object)[
                            'name' => 'Moscow',
                            'extra' => [
                                'codes' => [
                                    ['value' => 7495],
                                    ['value' => 7499],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Petersburg',
                            'extra' => [
                                'codes' => [
                                    ['value' => 7812],
                                ],
                            ],
                        ],
                    ],
                ],
                (object)[
                    'name' => 'Belarus',
                    'cities' => [
                        [
                            'name' => 'Minsk',
                            'extra' => [
                                'codes' => [
                                    ['value' => 375017],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $accessor = new NestedAccessor($input);
        $this->assertEquals(['Russia', 'Belarus'], $accessor->get('countries.name'));
        $this->assertEquals(['Moscow', 'Petersburg', 'Minsk'], $accessor->get('countries.cities.name'));
        $this->assertEquals([7495, 7499, 7812, 375017], $accessor->get('countries.cities.extra.codes.value'));

        $input = (object)[
            'a' => 1,
            'b' => (object)[
                'c' => 2,
            ]
        ];
        $accessor = new NestedAccessor($input);
        $this->assertEquals(1, $accessor->get('a'));
        $this->assertEquals((object)['c' => 2], $accessor->get('b'));
        $this->assertEquals(null, $accessor->get('c', false));
        $this->assertEquals(null, $accessor->get('c.d.e', false));
    }

    /**
     * @throws NestedAccessorException
     */
    public function testWriteSimple()
    {
        $accessor = new NestedAccessor($input);
        $accessor->set('test.a.a', 1);
        $this->assertEquals(['test' => ['a' => ['a' => 1]]], $accessor->get());
        $this->assertEquals(['test' => ['a' => ['a' => 1]]], $accessor->get(''));
        $this->assertEquals(['test' => ['a' => ['a' => 1]]], $accessor->get(null));
        $accessor->set('test.a.b', 2);
        $accessor->set('test.b.a', 3);
        $this->assertEquals(['a' => 1, 'b' => 2], $accessor->get('test.a'));
        $this->assertEquals(['a' => 3], $accessor->get('test.b'));
        $accessor->set('test.b.a', 33);
        $this->assertEquals(['a' => 33], $accessor->get('test.b'));
        $accessor->set('test.b.c', ['d' => 'e']);
        $this->assertEquals('e', $accessor->get('test.b.c.d'));
        $accessor->set('test.b', 0);
        $this->assertEquals(0, $accessor->get('test.b'));
        $this->assertEquals(null, $accessor->get('test.b.c.d', false));
        $accessor->set('test.b.c', (object)['d' => 'e']);
        $this->assertEquals((object)['d' => 'e'], $accessor->get('test.b.c', false));
        try {
            $accessor->set('test.b.c.f', 123);
            $this->expectError();
        } catch(NestedAccessorException $e) {
            $this->assertEquals(NestedAccessorException::CANNOT_SET_VALUE, $e->getCode());
            $this->assertEquals('f', $e->getData()['key']);
        }
        $this->assertEquals('e', $accessor->get('test.b.c.d'));
        $accessor->set('test.b.c.f', 123, false);
        $this->assertEquals(123, $accessor->get('test.b.c.f'));
        $this->assertEquals(['a' => 1, 'b' => 2], $accessor->get('test.a'));

        $input = ['a' => 1];
        $accessor = new NestedAccessor($input);
        $this->assertEquals(1, $accessor->get('a'));
        $this->assertEquals(['a' => 1], $accessor->get());
        $this->assertEquals(['a' => 1], $accessor->get(''));
        $accessor->set('a.b', 22);
        $this->assertEquals(['b' => 22], $accessor->get('a'));
        $accessor->set('c', 33);
        $this->assertEquals(['a' => ['b' => 22], 'c' => 33], $accessor->get());
    }

    public function testBadSource()
    {
        $input = 123;
        try {
            new NestedAccessor($input);
            $this->expectError();
        } catch(NestedAccessorException $e) {
            $this->assertEquals(NestedAccessorException::SOURCE_IS_SCALAR, $e->getCode());
            $this->assertEquals('integer', $e->getData()['source_type']);
        }

        $input = 123.5;
        try {
            new NestedAccessor($input);
            $this->expectError();
        } catch(NestedAccessorException $e) {
            $this->assertEquals(NestedAccessorException::SOURCE_IS_SCALAR, $e->getCode());
            $this->assertEquals('double', $e->getData()['source_type']);
        }

        $input = 'str';
        try {
            new NestedAccessor($input);
            $this->expectError();
        } catch(NestedAccessorException $e) {
            $this->assertEquals(NestedAccessorException::SOURCE_IS_SCALAR, $e->getCode());
            $this->assertEquals('string', $e->getData()['source_type']);
        }

        $input = true;
        try {
            new NestedAccessor($input);
            $this->expectError();
        } catch(NestedAccessorException $e) {
            $this->assertEquals(NestedAccessorException::SOURCE_IS_SCALAR, $e->getCode());
            $this->assertEquals('boolean', $e->getData()['source_type']);
        }
    }
}
