<?php

namespace Smoren\Schemator\Tests\Unit\NestedAccessor;

use Smoren\Schemator\Components\NestedAccessor;

class NestedAccessorDeleteTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForDeleteArray
     */
    public function testDeleteStrictSuccessArray($source, $path, $expected)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // When
        $accessor->delete($path);

        // Then
        $this->assertEquals($expected, $source);
    }

    public function dataProviderForDeleteArray(): array
    {
        return [
            [
                ['a' => []],
                'a',
                [],
            ],
            [
                ['a' => [1]],
                'a',
                [],
            ],
            [
                ['a' => ['b' => ['c' => [1, 2, 3, 4]]]],
                'a.b.c',
                ['a' => ['b' => []]],
            ],
            [
                ['a' => ['b' => ['c' => [1, 2, 3, 4], 'd' => 1]]],
                'a.b.c',
                ['a' => ['b' => ['d' => 1]]],
            ],
            [
                ['a' => ['b' => ['c' => [1, 2, 3, 4]]]],
                'a.b',
                ['a' => []],
            ],
        ];
    }
}
