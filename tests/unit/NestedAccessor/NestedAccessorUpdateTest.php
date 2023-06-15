<?php

namespace Smoren\Schemator\Tests\Unit\NestedAccessor;

use Smoren\Schemator\Components\NestedAccessor;
use Smoren\Schemator\Exceptions\PathNotExistException;
use Smoren\Schemator\Exceptions\PathNotWritableException;
use Smoren\Schemator\Tests\Unit\Fixtures\ClassWithAccessibleProperties;

class NestedAccessorUpdateTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForArray
     */
    public function testArray($source, $path, $value, $expected)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // When
        $accessor->update($path, $value);

        // Then
        $this->assertSame($value, $accessor->get($path));
        $this->assertEquals($expected, $source);
    }

    public function dataProviderForArray(): array
    {
        return [
            [
                ['a' => 1],
                'a',
                2,
                ['a' => 2],
            ],
            [
                ['a' => 1],
                ['a'],
                2,
                ['a' => 2],
            ],
            [
                ['a' => ['b' => ['c' => [0]]]],
                ['a', 'b', 'c'],
                'value',
                ['a' => ['b' => ['c' => 'value']]],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForPathNotExistError
     */
    public function testPathNotExistError($source, $path, $value)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // Then
        $this->expectException(PathNotExistException::class);

        // When
        $accessor->update($path, $value);
    }

    public function dataProviderForPathNotExistError(): array
    {
        return [
            [
                ['a' => 1],
                'b',
                2,
            ],
            [
                ['a' => 1],
                ['b'],
                2,
            ],
            [
                ['a' => ['b' => ['c' => [0]]]],
                ['a', 'b', 'd'],
                'value',
            ],
            [
                ['a' => ['b' => ['c' => [0]]]],
                ['a', 'a', 'c'],
                'value',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForPathNotWritableError
     */
    public function testPathNotWritableError($source, $path, $value)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // Then
        $this->expectException(PathNotWritableException::class);

        // When
        $accessor->update($path, $value);
    }

    public function dataProviderForPathNotWritableError(): array
    {
        return [
            [
                new ClassWithAccessibleProperties(),
                'protectedPropertyWithGetterAccess',
                2,
            ],
            [
                ['a' => new ClassWithAccessibleProperties()],
                'a.protectedPropertyWithGetterAccess',
                2,
            ],
            [
                ['a' => new ClassWithAccessibleProperties()],
                'a.privatePropertyWithGetterAccess',
                2,
            ],
        ];
    }
}
