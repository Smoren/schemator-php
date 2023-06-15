<?php

namespace Smoren\Schemator\Tests\Unit\NestedAccessor;

use Smoren\Schemator\Components\NestedAccessor;
use Smoren\Schemator\Exceptions\PathNotExistException;
use Smoren\Schemator\Exceptions\PathNotWritableException;
use Smoren\Schemator\Tests\Unit\Fixtures\ClassWithAccessibleProperties;

class NestedAccessorDeleteTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dataProviderForArray
     * @dataProvider dataProviderForArrayAccess
     * @dataProvider dataProviderForStdClass
     */
    public function testStrictSuccess($source, $path, $expected)
    {
        // Given
        $accessor = new NestedAccessor($source);

        // When
        $accessor->delete($path);

        // Then
        $this->assertEquals($expected, $source);
    }

    public function dataProviderForArray(): array
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

    public function dataProviderForArrayAccess(): array
    {
        return [
            [
                new \ArrayObject(['a' => []]),
                'a',
                new \ArrayObject([]),
            ],
            [
                new \ArrayObject(['a' => [1]]),
                'a',
                new \ArrayObject([]),
            ],
            [
                new \ArrayObject(['a' => ['b' => ['c' => [1, 2, 3, 4]]]]),
                'a.b.c',
                new \ArrayObject(['a' => ['b' => []]]),
            ],
            [
                ['a' => new \ArrayObject(['b' => ['c' => [1, 2, 3, 4], 'd' => 1]])],
                'a.b.c',
                ['a' => new \ArrayObject(['b' => ['d' => 1]])],
            ],
            [
                ['a' => new \ArrayObject(['b' => ['c' => [1, 2, 3, 4]]])],
                'a.b',
                ['a' => new \ArrayObject([])],
            ],
        ];
    }

    public function dataProviderForStdClass(): array
    {
        return [
            [
                (object)['a' => []],
                'a',
                (object)[],
            ],
            [
                (object)['a' => [1]],
                'a',
                (object)[],
            ],
            [
                (object)['a' => ['b' => ['c' => [1, 2, 3, 4]]]],
                'a.b.c',
                (object)['a' => ['b' => []]],
            ],
            [
                ['a' => (object)['b' => ['c' => [1, 2, 3, 4], 'd' => 1]]],
                'a.b.c',
                ['a' => (object)['b' => ['d' => 1]]],
            ],
            [
                ['a' => (object)['b' => ['c' => [1, 2, 3, 4]]]],
                'a.b',
                ['a' => (object)[]],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForNotExist
     */
    public function testNotExistError($source, $path)
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
    public function testNotExistNonStrict($source, $path, $expected)
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
    public function testNotWritableError($source, $path)
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
