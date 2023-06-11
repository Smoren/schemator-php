<?php

namespace Smoren\Schemator\Tests\Unit\NestedAccessor;

use Smoren\Schemator\Components\NestedAccessor;

class NestedAccessorSetTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForArray
     */
    public function testArraySimple($source, $path, $value, $expected)
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
}
