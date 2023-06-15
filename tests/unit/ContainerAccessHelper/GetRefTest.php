<?php

namespace Smoren\Schemator\Tests\Unit\ContainerAccessHelper;

use Codeception\Test\Unit;
use Smoren\Schemator\Helpers\ContainerAccessHelper;
use Smoren\Schemator\Tests\Unit\Fixtures\ClassWithAccessibleProperties;
use ArrayAccess;
use ArrayObject;
use stdClass;

class GetRefTest extends Unit
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
        $result = &ContainerAccessHelper::getRef($input, $key, $defaultValue);

        // Then
        $this->assertEquals($expected, $result);

        // And when
        $result = 'new result';

        // Then
        $this->assertEquals('new result', ContainerAccessHelper::get($input, $key));
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
        $result = &ContainerAccessHelper::getRef($input, $key, $defaultValue);

        // Then
        $this->assertEquals($expected, $result);

        // And when
        $result = 'new result';

        // Then
        $this->assertEquals('new result', ContainerAccessHelper::get($input, $key));
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
        $result = &ContainerAccessHelper::getRef($input, $key, $defaultValue);

        // Then
        $this->assertEquals($expected, $result);

        // And when
        $result = 'new result';

        // Then
        $this->assertEquals('new result', ContainerAccessHelper::get($input, $key));
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
        $result = &ContainerAccessHelper::getRef($input, $key, $defaultValue);

        // Then
        $this->assertEquals($expected, $result);

        // And when
        $result = 123;

        // Then
        $this->assertEquals(123, ContainerAccessHelper::get($input, $key));
    }

    public function fromObjectDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), 'publicProperty', null, 1],
            [new ClassWithAccessibleProperties(), 'publicProperty', 42, 1],
            [new ClassWithAccessibleProperties(), 'publicPropertyWithGetterAccess', null, 2],
            [new ClassWithAccessibleProperties(), 'publicPropertyWithGetterAccess', 42, 2],
        ];
    }

    /**
     * @param scalar $input
     * @param string $key
     * @param mixed $defaultValue
     * @return void
     * @dataProvider fromScalarErrorDataProvider
     */
    public function testFromScalarError($input, string $key, $defaultValue): void
    {
        try {
            // When
            ContainerAccessHelper::getRef($input, $key, $defaultValue);
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            // Then
            $type = gettype($input);
            $this->assertEquals(
                "Cannot get ref to key '{$key}' from container of type '{$type}'",
                $e->getMessage()
            );
        }
    }

    public function fromScalarErrorDataProvider(): array
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
