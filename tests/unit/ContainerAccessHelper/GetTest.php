<?php

namespace Smoren\Schemator\Tests\Unit\ContainerAccessHelper;

use Codeception\Test\Unit;
use Smoren\Schemator\Helpers\ContainerAccessHelper;
use Smoren\Schemator\Tests\Unit\Fixtures\ClassWithAccessibleProperties;
use ArrayAccess;
use ArrayObject;
use stdClass;

class GetTest extends Unit
{
    /**
     * @param array $input
     * @param string $key
     * @param mixed $defaultValue
     * @param mixed $expected
     * @return void
     * @dataProvider fromArrayDataProvider
     */
    public function testFromArray(array $input, string $key, $defaultValue, $expected): void
    {
        // When
        $result = ContainerAccessHelper::get($input, $key, $defaultValue);

        // Then
        $this->assertEquals($expected, $result);
    }

    public function fromArrayDataProvider(): array
    {
        return [
            [[], '', null, null],
            [[], '', 42, 42],
            [[], '0', null, null],
            [[], '0', 42, 42],
            [[], 'a', null, null],
            [[], 'b', 42, 42],
            [['a' => 1, 'b' => 2], '', null, null],
            [['a' => 1, 'b' => 2], '', 42, 42],
            [['a' => 1, 'b' => 2], '0', null, null],
            [['a' => 1, 'b' => 2], '0', 42, 42],
            [['a' => 1, 'b' => 2], '1', null, null],
            [['a' => 1, 'b' => 2], '1', 42, 42],
            [['a' => 1, 'b' => 2], '2', null, null],
            [['a' => 1, 'b' => 2], '2', 42, 42],
            [['a' => 1, 'b' => 2], 'a', 42, 1],
            [['a' => 1, 'b' => 2], 'b', 42, 2],
        ];
    }

    /**
     * @param ArrayAccess $input
     * @param string $key
     * @param mixed $defaultValue
     * @param mixed $expected
     * @return void
     * @dataProvider fromArrayAccessDataProvider
     */
    public function testFromArrayAccess(ArrayAccess $input, string $key, $defaultValue, $expected): void
    {
        // When
        $result = ContainerAccessHelper::get($input, $key, $defaultValue);

        // Then
        $this->assertEquals($expected, $result);
    }

    public function fromArrayAccessDataProvider(): array
    {
        $wrap = static function(array $input): ArrayAccess {
            return new ArrayObject($input);
        };

        return [
            [$wrap([]), '', null, null],
            [$wrap([]), '', 42, 42],
            [$wrap([]), '0', null, null],
            [$wrap([]), '0', 42, 42],
            [$wrap([]), 'a', null, null],
            [$wrap([]), 'b', 42, 42],
            [$wrap(['a' => 1, 'b' => 2]), '', null, null],
            [$wrap(['a' => 1, 'b' => 2]), '', 42, 42],
            [$wrap(['a' => 1, 'b' => 2]), '0', null, null],
            [$wrap(['a' => 1, 'b' => 2]), '0', 42, 42],
            [$wrap(['a' => 1, 'b' => 2]), '1', null, null],
            [$wrap(['a' => 1, 'b' => 2]), '1', 42, 42],
            [$wrap(['a' => 1, 'b' => 2]), '2', null, null],
            [$wrap(['a' => 1, 'b' => 2]), '2', 42, 42],
            [$wrap(['a' => 1, 'b' => 2]), 'a', 42, 1],
            [$wrap(['a' => 1, 'b' => 2]), 'b', 42, 2],
        ];
    }

    /**
     * @param stdClass $input
     * @param string $key
     * @param mixed $defaultValue
     * @param mixed $expected
     * @return void
     * @dataProvider fromStdClassDataProvider
     */
    public function testFromStdClass(stdClass $input, string $key, $defaultValue, $expected): void
    {
        // When
        $result = ContainerAccessHelper::get($input, $key, $defaultValue);

        // Then
        $this->assertEquals($expected, $result);
    }

    public function fromStdClassDataProvider(): array
    {
        $wrap = static function(array $input): object {
            return (object)$input;
        };

        return [
            [$wrap([]), '', null, null],
            [$wrap([]), '', 42, 42],
            [$wrap([]), '0', null, null],
            [$wrap([]), '0', 42, 42],
            [$wrap([]), 'a', null, null],
            [$wrap([]), 'b', 42, 42],
            [$wrap(['a' => 1, 'b' => 2]), '', null, null],
            [$wrap(['a' => 1, 'b' => 2]), '', 42, 42],
            [$wrap(['a' => 1, 'b' => 2]), '0', null, null],
            [$wrap(['a' => 1, 'b' => 2]), '0', 42, 42],
            [$wrap(['a' => 1, 'b' => 2]), '1', null, null],
            [$wrap(['a' => 1, 'b' => 2]), '1', 42, 42],
            [$wrap(['a' => 1, 'b' => 2]), '2', null, null],
            [$wrap(['a' => 1, 'b' => 2]), '2', 42, 42],
            [$wrap(['a' => 1, 'b' => 2]), 'a', 42, 1],
            [$wrap(['a' => 1, 'b' => 2]), 'b', 42, 2],
        ];
    }

    /**
     * @param object $input
     * @param string $key
     * @param mixed $defaultValue
     * @param mixed $expected
     * @return void
     * @dataProvider fromObjectDataProvider
     */
    public function testFromObject(object $input, string $key, $defaultValue, $expected): void
    {
        // When
        $result = ContainerAccessHelper::get($input, $key, $defaultValue);

        // Then
        $this->assertEquals($expected, $result);
    }

    public function fromObjectDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), '', null, null],
            [new ClassWithAccessibleProperties(), '', 42, 42],
            [new ClassWithAccessibleProperties(), '0', null, null],
            [new ClassWithAccessibleProperties(), '0', 42, 42],
            [new ClassWithAccessibleProperties(), 'unknownProperty', null, null],
            [new ClassWithAccessibleProperties(), 'unknownProperty', 42, 42],
            [new ClassWithAccessibleProperties(), 'publicProperty', null, 1],
            [new ClassWithAccessibleProperties(), 'publicProperty', 42, 1],
            [new ClassWithAccessibleProperties(), 'publicPropertyWithGetterAccess', null, 2],
            [new ClassWithAccessibleProperties(), 'publicPropertyWithGetterAccess', 42, 2],
            [new ClassWithAccessibleProperties(), 'protectedProperty', null, null],
            [new ClassWithAccessibleProperties(), 'protectedProperty', 42, 42],
            [new ClassWithAccessibleProperties(), 'protectedPropertyWithGetterAccess', null, 4],
            [new ClassWithAccessibleProperties(), 'protectedPropertyWithGetterAccess', 42, 4],
            [new ClassWithAccessibleProperties(), 'privateProperty', null, null],
            [new ClassWithAccessibleProperties(), 'privateProperty', 42, 42],
            [new ClassWithAccessibleProperties(), 'privatePropertyWithGetterAccess', null, 6],
            [new ClassWithAccessibleProperties(), 'privatePropertyWithGetterAccess', 42, 6],
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
