<?php

namespace Smoren\Schemator\Tests\Unit\ContainerAccessHelper;

use Smoren\Schemator\Helpers\ContainerAccessHelper;
use Smoren\Schemator\Tests\Unit\Fixtures\ClassWithAccessibleProperties;
use ArrayAccess;
use ArrayObject;
use stdClass;

class ExistsTest extends \Codeception\Test\Unit
{
    /**
     * @param array $input
     * @param string $key
     * @param bool $expected
     * @return void
     * @dataProvider arrayDataProvider
     */
    public function testArray(array $input, string $key, bool $expected): void
    {
        // When
        $result = ContainerAccessHelper::exists($input, $key);

        // Then
        $this->assertEquals($expected, $result);
    }

    public function arrayDataProvider(): array
    {
        return [
            [[], '', false],
            [[], '0', false],
            [[], 'a', false],
            [[], 'b', false],
            [['a' => 1, 'b' => 2], '', false],
            [['a' => 1, 'b' => 2], '0', false],
            [['a' => 1, 'b' => 2], '1', false],
            [['a' => 1, 'b' => 2], '2', false],
            [['a' => 1, 'b' => 2], 'a', true],
            [['a' => 1, 'b' => 2], 'b', true],
        ];
    }

    /**
     * @param ArrayAccess $input
     * @param string $key
     * @param bool $expected
     * @return void
     * @dataProvider arrayAccessDataProvider
     */
    public function testArrayAccess(ArrayAccess $input, string $key, bool $expected): void
    {
        // When
        $result = ContainerAccessHelper::exists($input, $key);

        // Then
        $this->assertEquals($expected, $result);
    }

    public function arrayAccessDataProvider(): array
    {
        $wrap = static function(array $input): ArrayAccess {
            return new ArrayObject($input);
        };

        return [
            [$wrap([]), '', false],
            [$wrap([]), '0', false],
            [$wrap([]), 'a', false],
            [$wrap([]), 'b', false],
            [$wrap(['a' => 1, 'b' => 2]), '', false],
            [$wrap(['a' => 1, 'b' => 2]), '0', false],
            [$wrap(['a' => 1, 'b' => 2]), '1', false],
            [$wrap(['a' => 1, 'b' => 2]), '2', false],
            [$wrap(['a' => 1, 'b' => 2]), 'a', true],
            [$wrap(['a' => 1, 'b' => 2]), 'b', true],
        ];
    }

    /**
     * @param stdClass $input
     * @param string $key
     * @param bool $expected
     * @return void
     * @dataProvider stdClassDataProvider
     */
    public function testStdClass(stdClass $input, string $key, bool $expected): void
    {
        // When
        $result = ContainerAccessHelper::exists($input, $key);

        // Then
        $this->assertEquals($expected, $result);
    }

    public function stdClassDataProvider(): array
    {
        $wrap = static function(array $input): object {
            return (object)$input;
        };

        return [
            [$wrap([]), '', false],
            [$wrap([]), '0', false],
            [$wrap([]), 'a', false],
            [$wrap([]), 'b', false],
            [$wrap(['a' => 1, 'b' => 2]), '', false],
            [$wrap(['a' => 1, 'b' => 2]), '0', false],
            [$wrap(['a' => 1, 'b' => 2]), '1', false],
            [$wrap(['a' => 1, 'b' => 2]), '2', false],
            [$wrap(['a' => 1, 'b' => 2]), 'a', true],
            [$wrap(['a' => 1, 'b' => 2]), 'b', true],
        ];
    }

    /**
     * @param object $input
     * @param string $key
     * @param bool $expected
     * @return void
     * @dataProvider objectDataProvider
     */
    public function testObject(object $input, string $key, bool $expected): void
    {
        // When
        $result = ContainerAccessHelper::exists($input, $key);

        // Then
        $this->assertEquals($expected, $result);
    }

    public function objectDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), '', false],
            [new ClassWithAccessibleProperties(), '0', false],
            [new ClassWithAccessibleProperties(), 'unknownProperty', false],
            [new ClassWithAccessibleProperties(), 'publicProperty', true],
            [new ClassWithAccessibleProperties(), 'publicPropertyWithGetterAccess', true],
            [new ClassWithAccessibleProperties(), 'protectedProperty', false],
            [new ClassWithAccessibleProperties(), 'protectedPropertyWithGetterAccess', true],
            [new ClassWithAccessibleProperties(), 'privateProperty', false],
            [new ClassWithAccessibleProperties(), 'privatePropertyWithGetterAccess', true],
        ];
    }

    /**
     * @param scalar $input
     * @param string $key
     * @return void
     * @dataProvider scalarDataProvider
     */
    public function testScalar($input, string $key): void
    {
        // When
        $result = ContainerAccessHelper::exists($input, $key);

        // Then
        $this->assertFalse($result);
    }

    public function scalarDataProvider(): array
    {
        return [
            ['', ''],
            ['', '0'],
            ['', '1'],
            ['', '2'],
            [0, ''],
            [0, '0'],
            [0, '1'],
            [0, '2'],
            [1, ''],
            [1, '0'],
            [1, '1'],
            [1, '2'],
            ['0', ''],
            ['0', '0'],
            ['0', '1'],
            ['0', '2'],
            ['1', ''],
            ['1', '0'],
            ['1', '1'],
            ['1', '2'],
            ['111', ''],
            ['111', '0'],
            ['111', '1'],
            ['111', '2'],
        ];
    }
}
