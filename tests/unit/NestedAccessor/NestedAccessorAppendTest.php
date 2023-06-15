<?php

namespace Smoren\Schemator\Tests\Unit\NestedAccessor;

use Smoren\Schemator\Components\NestedAccessor;
use Smoren\Schemator\Exceptions\PathNotArrayAccessibleException;
use Smoren\Schemator\Exceptions\PathNotExistException;

class NestedAccessorAppendTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForSuccess
     */
    public function testSuccess($source, $path, $value, $expected)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // When
        $accessor->append($path, $value);

        // Then
        $this->assertEquals($expected, $source);
    }

    public function dataProviderForSuccess(): array
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

    /**
     * @dataProvider dataProviderForNotExistError
     */
    public function testNotExistError($source, $path, $value)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // Then
        $this->expectException(PathNotExistException::class);

        // When
        $accessor->append($path, $value);
    }

    public function dataProviderForNotExistError(): array
    {
        return [
            [
                [],
                'a',
                1,
            ],
            [
                ['a' => [1]],
                'b',
                2,
            ],
            [
                ['a' => ['b' => ['c' => [1, 2, 3, 4]]]],
                'a.b.d',
                5,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForNotArrayAccessibleError
     */
    public function testNotArrayAccessibleError($source, $path, $value)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // Then
        $this->expectException(PathNotArrayAccessibleException::class);

        // When
        $accessor->append($path, $value);
    }

    public function dataProviderForNotArrayAccessibleError(): array
    {
        return [
            [
                ['a' => (object)[1]],
                'a',
                2,
            ],
            [
                ['a' => ['b' => ['c' => (object)[1, 2, 3, 4]]]],
                'a.b.c',
                5,
            ],
        ];
    }
}
