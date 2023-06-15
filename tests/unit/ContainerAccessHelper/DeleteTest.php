<?php

namespace Smoren\Schemator\Tests\Unit\ContainerAccessHelper;

use Codeception\Test\Unit;
use Smoren\Schemator\Helpers\ContainerAccessHelper;
use Smoren\Schemator\Tests\Unit\Fixtures\ClassWithAccessibleProperties;
use ArrayAccess;
use ArrayObject;
use stdClass;

class DeleteTest extends Unit
{
    /**
     * @param array $input
     * @param string|int $key
     * @param mixed $expected
     * @return void
     * @dataProvider fromArrayDataProvider
     */
    public function testFromArray(array $input, $key, $expected): void
    {
        // When
        ContainerAccessHelper::delete($input, $key);

        // Then
        $this->assertEquals($expected, $input);
    }

    public function fromArrayDataProvider(): array
    {
        return [
            [['a' => 1, 'b' => 2], 'a', ['b' => 2]],
            [['a' => 1, 'b' => 2], 'с', ['a' => 1, 'b' => 2]],
            [[1, 2], 1, [1]],
        ];
    }

    /**
     * @param ArrayAccess $input
     * @param string|int $key
     * @param mixed $expected
     * @return void
     * @dataProvider fromArrayAccessDataProvider
     */
    public function testFromArrayAccess(ArrayAccess $input, $key, $expected): void
    {
        // When
        ContainerAccessHelper::delete($input, $key);

        // Then
        $this->assertEquals($expected, $input);
    }

    public function fromArrayAccessDataProvider(): array
    {
        $wrap = fn (array $input) => new ArrayObject($input);

        return [
            [$wrap(['a' => 1, 'b' => 2]), 'a', $wrap(['b' => 2])],
            [$wrap(['a' => 1, 'b' => 2]), 'с', $wrap(['a' => 1, 'b' => 2])],
            [$wrap([1, 2]), 1, $wrap([1])],
        ];
    }

    /**
     * @param stdClass $input
     * @param string|int $key
     * @param mixed $expected
     * @return void
     * @dataProvider fromStdClassDataProvider
     */
    public function testFromStdClass(stdClass $input, $key, $expected): void
    {
        // When
        ContainerAccessHelper::delete($input, $key);

        // Then
        $this->assertEquals($expected, $input);
    }

    public function fromStdClassDataProvider(): array
    {
        $wrap = fn (array $input) => (object)$input;

        return [
            [$wrap(['a' => 1, 'b' => 2]), 'a', $wrap(['b' => 2])],
            [$wrap(['a' => 1, 'b' => 2]), 'с', $wrap(['a' => 1, 'b' => 2])],
            [$wrap([1, 2]), 1, $wrap([1])],
        ];
    }

    /**
     * @param object $input
     * @param string $key
     * @return void
     * @dataProvider errorDataProvider
     */
    public function testError(object $input, string $key): void
    {
        try {
            // When
            ContainerAccessHelper::delete($input, $key);
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            // Then
            $type = gettype($input);
            $this->assertSame("Cannot delete key from variable of type '{$type}'", $e->getMessage());
        }
    }

    public function errorDataProvider(): array
    {
        return [
            [fn () => 1, 'test'],
            [new ClassWithAccessibleProperties(), ''],
            [new ClassWithAccessibleProperties(), ''],
            [new ClassWithAccessibleProperties(), '0'],
            [new ClassWithAccessibleProperties(), '0'],
            [new ClassWithAccessibleProperties(), 'unknownProperty'],
            [new ClassWithAccessibleProperties(), 'unknownProperty'],
            [new ClassWithAccessibleProperties(), 'publicProperty'],
            [new ClassWithAccessibleProperties(), 'publicProperty'],
            [new ClassWithAccessibleProperties(), 'publicPropertyWithGetterAccess'],
            [new ClassWithAccessibleProperties(), 'publicPropertyWithGetterAccess'],
            [new ClassWithAccessibleProperties(), 'protectedProperty'],
            [new ClassWithAccessibleProperties(), 'protectedProperty'],
            [new ClassWithAccessibleProperties(), 'protectedPropertyWithGetterAccess'],
            [new ClassWithAccessibleProperties(), 'protectedPropertyWithGetterAccess'],
            [new ClassWithAccessibleProperties(), 'privateProperty'],
            [new ClassWithAccessibleProperties(), 'privateProperty'],
            [new ClassWithAccessibleProperties(), 'privatePropertyWithGetterAccess'],
            [new ClassWithAccessibleProperties(), 'privatePropertyWithGetterAccess'],
        ];
    }

    /**
     * @param scalar $input
     * @param string $key
     * @param mixed $defaultValue
     * @return void
     * @dataProvider fromScalarDataProvider
     */
    public function testFromScalar($input, string $key, $defaultValue): void
    {
        // When
        $result = ContainerAccessHelper::get($input, $key, $defaultValue);

        // Then
        $this->assertEquals($defaultValue, $result);
    }

    public function fromScalarDataProvider(): array
    {
        return [
            ['', '', null],
            ['', '', 42],
            ['', '0', null],
            ['', '0', 42],
            ['', '1', null],
            ['', '1', 42],
            ['', '2', null],
            ['', '2', 42],
            [0, '', null],
            [0, '', 42],
            [0, '0', null],
            [0, '0', 42],
            [0, '1', null],
            [0, '1', 42],
            [0, '2', null],
            [0, '2', 42],
            [1, '', null],
            [1, '', 42],
            [1, '0', null],
            [1, '0', 42],
            [1, '1', null],
            [1, '1', 42],
            [1, '2', null],
            [1, '2', 42],
            ['0', '', null],
            ['0', '', 42],
            ['0', '0', null],
            ['0', '0', 42],
            ['0', '1', null],
            ['0', '1', 42],
            ['0', '2', null],
            ['0', '2', 42],
            ['1', '', null],
            ['1', '', 42],
            ['1', '0', null],
            ['1', '0', 42],
            ['1', '1', null],
            ['1', '1', 42],
            ['1', '2', null],
            ['1', '2', 42],
            ['111', '', null],
            ['111', '', 42],
            ['111', '0', null],
            ['111', '0', 42],
            ['111', '1', null],
            ['111', '1', 42],
            ['111', '2', null],
            ['111', '2', 42],
        ];
    }
}
