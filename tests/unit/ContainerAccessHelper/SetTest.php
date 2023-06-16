<?php

declare(strict_types=1);

namespace Smoren\Schemator\Tests\Unit\ContainerAccessHelper;

use Codeception\Test\Unit;
use Smoren\Schemator\Helpers\ContainerAccessHelper;
use Smoren\Schemator\Tests\Unit\Fixtures\ClassWithAccessibleProperties;
use ArrayAccess;
use ArrayObject;
use stdClass;

class SetTest extends Unit
{
    /**
     * @param array $input
     * @param string $key
     * @param mixed $value
     * @return void
     * @dataProvider toArrayDataProvider
     */
    public function testToArray(array $input, string $key, $value): void
    {
        // When
        ContainerAccessHelper::set($input, $key, $value);

        // Then
        $result = ContainerAccessHelper::get($input, $key);
        $this->assertEquals($value, $result);
    }

    public function toArrayDataProvider(): array
    {
        return [
            [[], '', null],
            [[], '', 42],
            [[], '0', null],
            [[], '0', 42],
            [[], 'a', null],
            [[], 'b', 42],
            [['a' => 1, 'b' => 2], '', null],
            [['a' => 1, 'b' => 2], '', 42],
            [['a' => 1, 'b' => 2], '0', null],
            [['a' => 1, 'b' => 2], '0', 42],
            [['a' => 1, 'b' => 2], '1', null],
            [['a' => 1, 'b' => 2], '1', 42],
            [['a' => 1, 'b' => 2], '2', null],
            [['a' => 1, 'b' => 2], '2', 42],
            [['a' => 1, 'b' => 2], 'a', 42],
            [['a' => 1, 'b' => 2], 'b', 42],
        ];
    }

    /**
     * @param ArrayAccess $input
     * @param string $key
     * @param mixed $value
     * @return void
     * @dataProvider toArrayAccessDataProvider
     */
    public function testToArrayAccess(ArrayAccess $input, string $key, $value): void
    {
        // When
        ContainerAccessHelper::set($input, $key, $value);

        // Then
        $result = ContainerAccessHelper::get($input, $key);
        $this->assertEquals($value, $result);
    }

    public function toArrayAccessDataProvider(): array
    {
        $wrap = static function(array $input): ArrayAccess {
            return new ArrayObject($input);
        };

        return [
            [$wrap([]), '', null],
            [$wrap([]), '', 42],
            [$wrap([]), '0', null],
            [$wrap([]), '0', 42],
            [$wrap([]), 'a', null],
            [$wrap([]), 'b', 42],
            [$wrap(['a' => 1, 'b' => 2]), '', null],
            [$wrap(['a' => 1, 'b' => 2]), '', 42],
            [$wrap(['a' => 1, 'b' => 2]), '0', null],
            [$wrap(['a' => 1, 'b' => 2]), '0', 42],
            [$wrap(['a' => 1, 'b' => 2]), '1', null],
            [$wrap(['a' => 1, 'b' => 2]), '1', 42],
            [$wrap(['a' => 1, 'b' => 2]), '2', null],
            [$wrap(['a' => 1, 'b' => 2]), '2', 42],
            [$wrap(['a' => 1, 'b' => 2]), 'a', 42],
            [$wrap(['a' => 1, 'b' => 2]), 'b', 42],
        ];
    }

    /**
     * @param stdClass $input
     * @param string $key
     * @param mixed $value
     * @return void
     * @dataProvider toStdClassDataProvider
     */
    public function testToStdClass(stdClass $input, string $key, $value): void
    {
        // When
        ContainerAccessHelper::set($input, $key, $value);

        // Then
        $result = ContainerAccessHelper::get($input, $key);
        $this->assertEquals($value, $result);
    }

    public function toStdClassDataProvider(): array
    {
        $wrap = static function(array $input): object {
            return (object)$input;
        };

        return [
            [$wrap([]), '', null],
            [$wrap([]), '', 42],
            [$wrap([]), '0', null],
            [$wrap([]), '0', 42],
            [$wrap([]), 'a', null],
            [$wrap([]), 'b', 42],
            [$wrap(['a' => 1, 'b' => 2]), '', null],
            [$wrap(['a' => 1, 'b' => 2]), '', 42],
            [$wrap(['a' => 1, 'b' => 2]), '0', null],
            [$wrap(['a' => 1, 'b' => 2]), '0', 42],
            [$wrap(['a' => 1, 'b' => 2]), '1', null],
            [$wrap(['a' => 1, 'b' => 2]), '1', 42],
            [$wrap(['a' => 1, 'b' => 2]), '2', null],
            [$wrap(['a' => 1, 'b' => 2]), '2', 42],
            [$wrap(['a' => 1, 'b' => 2]), 'a', 42],
            [$wrap(['a' => 1, 'b' => 2]), 'b', 42],
        ];
    }

    /**
     * @param object $input
     * @param string $key
     * @param mixed $value
     * @return void
     * @dataProvider toObjectDataProvider
     */
    public function testToObject(object $input, string $key, $value): void
    {
        // When
        ContainerAccessHelper::set($input, $key, $value);

        // Then
        $result = ContainerAccessHelper::get($input, $key);
        $this->assertEquals($value, $result);
    }

    public function toObjectDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), 'publicProperty', 42],
            [new ClassWithAccessibleProperties(), 'publicPropertyWithMethodsAccess', 42],
            [new ClassWithAccessibleProperties(), 'protectedPropertyWithMethodsAccess', 42],
            [new ClassWithAccessibleProperties(), 'privatePropertyWithMethodsAccess', 42],
        ];
    }

    /**
     * @param object $input
     * @param string $key
     * @param mixed $value
     * @return void
     * @dataProvider toObjectFailDataProvider
     */
    public function testToObjectFail(object $input, string $key, $value): void
    {
        try {
            // When
            ContainerAccessHelper::set($input, $key, $value);
            $this->fail();
        } catch(\InvalidArgumentException $e) {
            // Then
            $this->assertEquals("Property '".get_class($input)."::{$key}' is not writable", $e->getMessage());
        }
    }

    public function toObjectFailDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), 'unknownProperty', 42],
            [new ClassWithAccessibleProperties(), 'protectedProperty', 42],
            [new ClassWithAccessibleProperties(), 'privateProperty', 42],
        ];
    }

    /**
     * @param scalar $input
     * @param string $key
     * @param mixed $value
     * @return void
     * @dataProvider toScalarDataProvider
     */
    public function testToScalar($input, string $key, $value): void
    {
        try {
            // When
            ContainerAccessHelper::set($input, $key, $value);
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            $type = gettype($input);
            $this->assertSame("Cannot set value to variable of type '{$type}'", $e->getMessage());
        }

        // Then
        $result = ContainerAccessHelper::get($input, $key);
        $this->assertNull($result);
    }

    public function toScalarDataProvider(): array
    {
        return [
            ['', '', null],
            ['', '', 42],
            ['', '0', 42],
            ['', '1', 42],
            ['', '2', 42],
            [0, '', 42],
            [0, '0', 42],
            [0, '1', 42],
            [0, '2', 42],
            [1, '', 42],
            [1, '0', 42],
            [1, '1', 42],
            [1, '2', 42],
            ['0', '', 42],
            ['0', '0', 42],
            ['0', '1', 42],
            ['0', '2', 42],
            ['1', '', 42],
            ['1', '0', 42],
            ['1', '1', 42],
            ['1', '2', 42],
            ['111', '', 42],
            ['111', '0', 42],
            ['111', '1', 42],
            ['111', '2', 42],
        ];
    }
}
