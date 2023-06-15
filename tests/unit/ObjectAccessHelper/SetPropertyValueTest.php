<?php

namespace Smoren\Schemator\Tests\Unit\ObjectAccessHelper;

use Codeception\Test\Unit;
use Smoren\Schemator\Helpers\ObjectAccessHelper;
use Smoren\Schemator\Tests\Unit\Fixtures\ClassWithAccessibleProperties;
use stdClass;

class SetPropertyValueTest extends Unit
{
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
        ObjectAccessHelper::setPropertyValue($input, $key, $value);

        // Then
        $result = ObjectAccessHelper::getPropertyValue($input, $key);
        $this->assertSame($value, $result);
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
     * @dataProvider toObjectSuccessDataProvider
     */
    public function testToObjectSuccess(object $input, string $key, $value): void
    {
        // When
        ObjectAccessHelper::setPropertyValue($input, $key, $value);

        // Then
        $result = ObjectAccessHelper::getPropertyValue($input, $key);
        $this->assertSame($value, $result);
    }

    public function toObjectSuccessDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), 'publicProperty', 42],
            [new ClassWithAccessibleProperties(), 'publicPropertyWithGetterAccess', 42],
            [new ClassWithAccessibleProperties(), 'protectedPropertyWithGetterAccess', 42],
            [new ClassWithAccessibleProperties(), 'privatePropertyWithGetterAccess', 42],
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
            ObjectAccessHelper::setPropertyValue($input, $key, $value);
            $this->fail();
        } catch(\InvalidArgumentException $e) {
            // Then
            $this->assertSame("Property '".get_class($input)."::{$key}' is not writable", $e->getMessage());
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
}
