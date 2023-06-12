<?php

namespace Smoren\Schemator\Tests\Unit\NestedAccessor;

use Smoren\Schemator\Components\NestedAccessor;

class NestedAccessorSetTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForArray
     */
    public function testArray($source, $path, $value, $expected)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // When
        $accessor->set($path, $value);

        // Then
        $this->assertSame($value, $accessor->get($path));
        $this->assertEquals($expected, $source);
    }

    /**
     * @dataProvider dataProviderForAppendSuccess
     */
    public function testAppendSuccess($source, $path, $value, $expected)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // When
        $accessor->append($path, $value);

        // Then
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

    public function dataProviderForAppendSuccess(): array
    {
        return [
            [
                [],
                null,
                1,
                [1],
            ],
            [
                ['a' => []],
                'a',
                1,
                ['a' => [1]],
            ],
            [
                ['a' => [1]],
                'a',
                2,
                ['a' => [1, 2]],
            ],
            [
                ['a' => ['b' => ['c' => [1, 2, 3, 4]]]],
                'a.b.c',
                5,
                ['a' => ['b' => ['c' => [1, 2, 3, 4, 5]]]],
            ],
            [
                ['a' => ['b' => ['c' => [1, 2, 3, 4]]]],
                'a',
                5,
                ['a' => ['b' => ['c' => [1, 2, 3, 4]], 5]],
            ],
            [
                ['a' => ['b' => ['c' => [1, 2, 3, 4]]]],
                'a.b',
                5,
                ['a' => ['b' => ['c' => [1, 2, 3, 4], 5]]],
            ],
        ];
    }
}
