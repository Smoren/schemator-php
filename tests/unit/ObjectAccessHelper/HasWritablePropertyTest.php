<?php

namespace Smoren\Schemator\Tests\Unit\ObjectAccessHelper;

use Codeception\Test\Unit;
use Smoren\Schemator\Helpers\ObjectAccessHelper;
use Smoren\Schemator\Tests\Unit\Fixtures\ClassWithAccessibleProperties;
use stdClass;

class HasWritablePropertyTest extends Unit
{
    /**
     * @param stdClass $input
     * @param string $key
     * @return void
     * @dataProvider toStdClassTrueDataProvider
     */
    public function testToStdClassTrue(stdClass $input, string $key): void
    {
        // When
        $result = ObjectAccessHelper::hasWritableProperty($input, $key);

        // Then
        $this->assertTrue($result);
    }

    public function toStdClassTrueDataProvider(): array
    {
        $wrap = static function(array $input): object {
            return (object)$input;
        };

        return [
            [$wrap(['a' => 1, 'b' => 2]), 'a'],
            [$wrap(['a' => 1, 'b' => 2]), 'b'],
        ];
    }

    /**
     * @param stdClass $input
     * @param string $key
     * @return void
     * @dataProvider toStdClassFalseDataProvider
     */
    public function testToStdClassFalse(stdClass $input, string $key): void
    {
        // When
        $result = ObjectAccessHelper::hasWritableProperty($input, $key);

        // Then
        $this->assertFalse($result);
    }

    public function toStdClassFalseDataProvider(): array
    {
        $wrap = static function(array $input): object {
            return (object)$input;
        };

        return [
            [$wrap([]), ''],
            [$wrap([]), ''],
            [$wrap([]), '0'],
            [$wrap([]), '0'],
            [$wrap([]), 'a'],
            [$wrap([]), 'b'],
            [$wrap(['a' => 1, 'b' => 2]), ''],
            [$wrap(['a' => 1, 'b' => 2]), ''],
            [$wrap(['a' => 1, 'b' => 2]), '0'],
            [$wrap(['a' => 1, 'b' => 2]), '0'],
            [$wrap(['a' => 1, 'b' => 2]), '1'],
            [$wrap(['a' => 1, 'b' => 2]), '1'],
            [$wrap(['a' => 1, 'b' => 2]), '2'],
            [$wrap(['a' => 1, 'b' => 2]), '2'],
        ];
    }

    /**
     * @param object $input
     * @param string $key
     * @return void
     * @dataProvider toObjectTrueDataProvider
     */
    public function testToObjectTrue(object $input, string $key): void
    {
        // When
        $result = ObjectAccessHelper::hasWritableProperty($input, $key);

        // Then
        $this->assertTrue($result);
    }

    public function toObjectTrueDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), 'publicProperty'],
            [new ClassWithAccessibleProperties(), 'publicPropertyWithGetterAccess'],
            [new ClassWithAccessibleProperties(), 'protectedPropertyWithGetterAccess'],
            [new ClassWithAccessibleProperties(), 'privatePropertyWithGetterAccess'],
        ];
    }

    /**
     * @param object $input
     * @param string $key
     * @return void
     * @dataProvider toObjectFalseDataProvider
     */
    public function testToObjectFalse(object $input, string $key): void
    {
        // When
        $result = ObjectAccessHelper::hasWritableProperty($input, $key);

        // Then
        $this->assertFalse($result);
    }

    public function toObjectFalseDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), 'unknownProperty'],
            [new ClassWithAccessibleProperties(), 'protectedProperty'],
            [new ClassWithAccessibleProperties(), 'privateProperty'],
        ];
    }
}
