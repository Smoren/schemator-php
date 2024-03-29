<?php

declare(strict_types=1);

namespace Smoren\Schemator\Tests\Unit\NestedAccessor;

use Smoren\Schemator\Components\NestedAccessor;
use Smoren\Schemator\Exceptions\PathNotExistException;
use Smoren\Schemator\Exceptions\PathNotWritableException;
use Smoren\Schemator\Tests\Unit\Fixtures\ClassWithAccessibleProperties;

class NestedAccessorUpdateTest extends \Codeception\Test\Unit
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

    public function dataProviderForArrayAccess(): array
    {
        return [
            [
                new \ArrayObject(['a' => 1]),
                'a',
                2,
                new \ArrayObject(['a' => 2]),
            ],
            [
                new \ArrayObject(['a' => 1]),
                ['a'],
                2,
                new \ArrayObject(['a' => 2]),
            ],
            [
                ['a' => new \ArrayObject(['b' => ['c' => [0]]])],
                ['a', 'b', 'c'],
                'value',
                ['a' => new \ArrayObject(['b' => ['c' => 'value']])],
            ],
        ];
    }

    public function dataProviderForStdClass(): array
    {
        return [
            [
                (object)['a' => 1],
                'a',
                2,
                (object)['a' => 2],
            ],
            [
                (object)['a' => 1],
                ['a'],
                2,
                (object)['a' => 2],
            ],
            [
                ['a' => (object)['b' => ['c' => [0]]]],
                ['a', 'b', 'c'],
                'value',
                ['a' => (object)['b' => ['c' => 'value']]],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForObject
     */
    public function testObject($source, $path, $value)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // When
        $accessor->update($path, $value);

        // Then
        $this->assertEquals($value, $accessor->get($path));
    }

    public function dataProviderForObject(): array
    {
        return [
            [
                new ClassWithAccessibleProperties(),
                'protectedPropertyWithMethodsAccess',
                22,
            ],
            [
                ['a' => new ClassWithAccessibleProperties()],
                'a.protectedPropertyWithMethodsAccess',
                23,
            ],
            [
                ['a' => new ClassWithAccessibleProperties()],
                'a.privatePropertyWithMethodsAccess',
                24,
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
            [
                new ClassWithAccessibleProperties(),
                'protectedProperty',
                2,
            ],
            [
                ['a' => new ClassWithAccessibleProperties()],
                'a.protectedProperty',
                2,
            ],
            [
                ['a' => new ClassWithAccessibleProperties()],
                'a.privateProperty',
                2,
            ],
        ];
    }
}
