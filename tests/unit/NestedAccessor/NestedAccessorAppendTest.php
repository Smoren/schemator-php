<?php

namespace Smoren\Schemator\Tests\Unit\NestedAccessor;

use Smoren\Schemator\Components\NestedAccessor;

class NestedAccessorAppendTest extends \Codeception\Test\Unit
{
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
