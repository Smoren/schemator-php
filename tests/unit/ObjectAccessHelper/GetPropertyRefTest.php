<?php

declare(strict_types=1);

namespace Smoren\Schemator\Tests\Unit\ObjectAccessHelper;

use Codeception\Test\Unit;
use Smoren\Schemator\Helpers\ObjectAccessHelper;
use Smoren\Schemator\Tests\Unit\Fixtures\ClassWithAccessibleProperties;
use stdClass;

class GetPropertyRefTest extends Unit
{
    /**
     * @param stdClass $input
     * @param string $key
     * @param mixed $expected
     * @return void
     * @dataProvider fromStdClassSuccessDataProvider
     */
    public function testFromStdClassSuccess(stdClass $input, string $key, $expected): void
    {
        // When
        $result = &ObjectAccessHelper::getPropertyRef($input, $key);

        // Then
        $this->assertSame($expected, $result);

        // And when
        $result = 'new result';

        // Then
        $this->assertEquals('new result', ObjectAccessHelper::getPropertyValue($input, $key));
    }

    public function fromStdClassSuccessDataProvider(): array
    {
        $wrap = fn (array $input) => (object)$input;

        return [
            [$wrap(['a' => 1, 'b' => 2]), 'a', 1],
            [$wrap(['a' => 1, 'b' => 2]), 'b', 2],
            [$wrap([]), 'b', null],
            [$wrap(['a' => 1]), 'b', null],
        ];
    }

    /**
     * @param object $input
     * @param string $key
     * @param mixed $expected
     * @return void
     * @dataProvider fromObjectSuccessDataProvider
     */
    public function testFromObjectSuccess(object $input, string $key, $expected): void
    {
        // When
        $result = &ObjectAccessHelper::getPropertyRef($input, $key);

        // Then
        $this->assertSame($expected, $result);

        // And when
        $result = 123;

        // Then
        $this->assertEquals(123, ObjectAccessHelper::getPropertyValue($input, $key));
    }

    public function fromObjectSuccessDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), 'publicProperty', 1],
            [new ClassWithAccessibleProperties(), 'publicProperty', 1],
            [new ClassWithAccessibleProperties(), 'publicPropertyWithMethodsAccess', 2],
            [new ClassWithAccessibleProperties(), 'publicPropertyWithMethodsAccess', 2],
        ];
    }

    /**
     * @param object $input
     * @param string $key
     * @return void
     * @dataProvider fromObjectFailDataProvider
     */
    public function testFromObjectGetFail(object $input, string $key): void
    {
        // When
        $proxy = ObjectAccessHelper::getPropertyRef($input, $key);

        try {
            $proxy->getValue();
            $this->fail();
        } catch (\BadMethodCallException $e) {
            // Then
            $this->assertSame("Property '" . get_class($input) . "::{$key}' is not readable", $e->getMessage());
        }
    }

    /**
     * @param object $input
     * @param string $key
     * @return void
     * @dataProvider fromObjectFailDataProvider
     */
    public function testFromObjectSetFail(object $input, string $key): void
    {
        // When
        $proxy = ObjectAccessHelper::getPropertyRef($input, $key);

        try {
            $proxy->setValue(150);
            $this->fail();
        } catch (\BadMethodCallException $e) {
            // Then
            $this->assertSame("Property '" . get_class($input) . "::{$key}' is not writable", $e->getMessage());
        }
    }

    public function fromObjectFailDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), ''],
            [new ClassWithAccessibleProperties(), ''],
            [new ClassWithAccessibleProperties(), '0'],
            [new ClassWithAccessibleProperties(), '0'],
            [new ClassWithAccessibleProperties(), 'unknownProperty'],
            [new ClassWithAccessibleProperties(), 'unknownProperty'],
            [new ClassWithAccessibleProperties(), 'protectedProperty'],
            [new ClassWithAccessibleProperties(), 'protectedProperty'],
            [new ClassWithAccessibleProperties(), 'privateProperty'],
            [new ClassWithAccessibleProperties(), 'privateProperty'],
        ];
    }

    /**
     * @param object $input
     * @param string $key
     * @param $expected
     * @return void
     * @dataProvider fromObjectProxySuccessDataProvider
     */
    public function testFromObjectProxySuccess(object $input, string $key, $expected): void
    {
        // When
        $proxy = ObjectAccessHelper::getPropertyRef($input, $key);

        // Then
        $this->assertEquals($expected, $proxy->getValue());
    }

    public function fromObjectProxySuccessDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), 'protectedPropertyWithMethodsAccess', 4],
            [new ClassWithAccessibleProperties(), 'privatePropertyWithMethodsAccess', 6],
        ];
    }
}
