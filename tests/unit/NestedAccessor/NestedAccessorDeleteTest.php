<?php

namespace Smoren\Schemator\Tests\Unit\NestedAccessor;

use Smoren\Schemator\Components\NestedAccessor;
use Smoren\Schemator\Exceptions\PathNotExistException;
use Smoren\Schemator\Exceptions\PathNotWritableException;
use Smoren\Schemator\Tests\Unit\Fixtures\ClassWithAccessibleProperties;

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

    /**
     * @dataProvider dataProviderForNotExist
     */
    public function testDeleteNotExistError($source, $path)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // Then
        $this->expectException(PathNotExistException::class);

        // When
        $accessor->delete($path);
    }

    /**
     * @dataProvider dataProviderForNotExist
     */
    public function testDeleteNotExistNonStrict($source, $path, $expected)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // When
        $accessor->delete($path, false);

        // Then
        $this->assertEquals($expected, $source);
        $this->assertEquals($expected, $accessor->get());
    }

    public function dataProviderForNotExist(): array
    {
        return [
            [
                ['a' => 1],
                'b',
                ['a' => 1],
            ],
            [
                ['a' => ['b' => ['c' => 1]]],
                'a.b.d',
                ['a' => ['b' => ['c' => 1]]],
            ],
            [
                ['a' => ['b' => ['c' => 1]]],
                'a.d',
                ['a' => ['b' => ['c' => 1]]],
            ],
            [
                ['a' => ['b' => ['c' => 1]]],
                'd',
                ['a' => ['b' => ['c' => 1]]],
            ],
            [
                ['a' => ['b' => ['c' => 1]]],
                'd.e',
                ['a' => ['b' => ['c' => 1]]],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForNotWritableError
     */
    public function testDeleteNotWritableError($source, $path)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // Then
        $this->expectException(PathNotWritableException::class);

        // When
        $accessor->delete($path);
    }

    public function dataProviderForNotWritableError(): array
    {
        return [
            [new ClassWithAccessibleProperties(), 'publicProperty'],
            [new ClassWithAccessibleProperties(), 'publicPropertyWithGetterAccess'],
            [new ClassWithAccessibleProperties(), 'protectedPropertyWithGetterAccess'],
            [new ClassWithAccessibleProperties(), 'privatePropertyWithGetterAccess'],
        ];
    }
}
