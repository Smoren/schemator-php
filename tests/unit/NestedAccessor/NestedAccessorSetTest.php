<?php

namespace Smoren\Schemator\Tests\Unit\NestedAccessor;

use Smoren\Schemator\Components\NestedAccessor;

class NestedAccessorSetTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForArray
     * @dataProvider dataProviderForArrayAccess
     * @dataProvider dataProviderForStdClass
     */
    public function testSuccess($source, $path, $value, $expected)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // When
        $accessor->set($path, $value);

        // Then
        $this->assertSame($value, $accessor->get($path));
        $this->assertEquals($expected, $source);
    }

    public function dataProviderForArray(): array
    {
        return [
            [
                [],
                null,
                1,
                1,
            ],
            [
                [],
                0,
                1,
                [1],
            ],
            [
                [],
                '0',
                1,
                [1],
            ],
            [
                [],
                '0.1',
                11,
                [[1 => 11]],
            ],
            [
                [],
                'a.b.c',
                [1],
                ['a' => ['b' => ['c' => [1]]]],
            ],
            [
                ['a' => []],
                'a.b.c',
                [1],
                ['a' => ['b' => ['c' => [1]]]],
            ],
            [
                ['a' => 1],
                'a.b.c',
                [1],
                ['a' => ['b' => ['c' => [1]]]],
            ],
            [
                ['a' => [1]],
                'a.b.c',
                [1],
                ['a' => [1, 'b' => ['c' => [1]]]],
            ],
            [
                ['a' => [1]],
                ['a', 'b', 'c'],
                [1],
                ['a' => [1, 'b' => ['c' => [1]]]],
            ],
            [
                ['a' => ['b' => ['c' => [0]]]],
                ['a', 'b', 'd'],
                [1],
                ['a' => ['b' => ['c' => [0], 'd' => [1]]]],
            ],
        ];
    }

    public function dataProviderForArrayAccess(): array
    {
        return [
            [
                new \ArrayObject([]),
                null,
                1,
                1,
            ],
            [
                new \ArrayObject([]),
                0,
                1,
                new \ArrayObject([1]),
            ],
            [
                new \ArrayObject([]),
                '0',
                1,
                new \ArrayObject([1]),
            ],
            [
                new \ArrayObject([]),
                '0.1',
                11,
                new \ArrayObject([[1 => 11]]),
            ],
            [
                new \ArrayObject([]),
                'a.b.c',
                [1],
                new \ArrayObject(['a' => ['b' => ['c' => [1]]]]),
            ],
            [
                ['a' => new \ArrayObject([])],
                'a.b.c',
                [1],
                ['a' => new \ArrayObject(['b' => ['c' => [1]]])],
            ],
            [
                new \ArrayObject(['a' => 1]),
                'a.b.c',
                [1],
                new \ArrayObject(['a' => ['b' => ['c' => [1]]]]),
            ],
            [
                ['a' => new \ArrayObject([1])],
                'a.b.c',
                [1],
                ['a' => new \ArrayObject([1, 'b' => ['c' => [1]]])],
            ],
            [
                ['a' => new \ArrayObject([1])],
                ['a', 'b', 'c'],
                [1],
                ['a' => new \ArrayObject([1, 'b' => ['c' => [1]]])],
            ],
            [
                ['a' => new \ArrayObject(['b' => ['c' => [0]]])],
                ['a', 'b', 'd'],
                [1],
                ['a' => new \ArrayObject(['b' => ['c' => [0], 'd' => [1]]])],
            ],
        ];
    }

    public function dataProviderForStdClass(): array
    {
        return [
            [
                (object)[],
                null,
                1,
                1,
            ],
            [
                (object)[],
                0,
                1,
                (object)[1],
            ],
            [
                (object)[],
                '0',
                1,
                (object)[1],
            ],
            [
                (object)[],
                '0.1',
                11,
                (object)[[1 => 11]],
            ],
            [
                (object)[],
                'a.b.c',
                [1],
                (object)['a' => ['b' => ['c' => [1]]]],
            ],
            [
                (object)['a' => []],
                'a.b.c',
                [1],
                (object)['a' => ['b' => ['c' => [1]]]],
            ],
            [
                (object)['a' => 1],
                'a.b.c',
                [1],
                (object)['a' => ['b' => ['c' => [1]]]],
            ],
            [
                (object)['a' => [1]],
                'a.b.c',
                [1],
                (object)['a' => [1, 'b' => ['c' => [1]]]],
            ],
            [
                ['a' => (object)[1]],
                ['a', 'b', 'c'],
                [1],
                ['a' => (object)[1, 'b' => ['c' => [1]]]],
            ],
            [
                ['a' => ['b' => (object)['c' => [0]]]],
                ['a', 'b', 'd'],
                [1],
                ['a' => ['b' => (object)['c' => [0], 'd' => [1]]]],
            ],
        ];
    }
}
