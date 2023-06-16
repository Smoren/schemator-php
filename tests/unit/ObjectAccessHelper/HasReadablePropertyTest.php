<?php

declare(strict_types=1);

namespace Smoren\Schemator\Tests\Unit\ObjectAccessHelper;

use Codeception\Test\Unit;
use Smoren\Schemator\Helpers\ObjectAccessHelper;
use Smoren\Schemator\Tests\Unit\Fixtures\ClassWithAccessibleProperties;
use stdClass;

class HasReadablePropertyTest extends Unit
{
    /**
     * @param stdClass $input
     * @param string $key
     * @return void
     * @dataProvider fromStdClassTrueDataProvider
     */
    public function testFromStdClassTrue(stdClass $input, string $key): void
    {
        // When
        $result = ObjectAccessHelper::hasReadableProperty($input, $key);

        // Then
        $this->assertTrue($result);
    }

    public function fromStdClassTrueDataProvider(): array
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
     * @dataProvider fromStdClassFalseDataProvider
     */
    public function testFromStdClassFalse(stdClass $input, string $key): void
    {
        // When
        $result = ObjectAccessHelper::hasReadableProperty($input, $key);

        // Then
        $this->assertFalse($result);
    }

    public function fromStdClassFalseDataProvider(): array
    {
        $wrap = static function(array $input): object {
            return (object)$input;
        };

        return [
            [$wrap([]), ''],
            [$wrap([]), '0'],
            [$wrap([]), 'a'],
            [$wrap([]), 'b'],
            [$wrap(['a' => 1, 'b' => 2]), ''],
            [$wrap(['a' => 1, 'b' => 2]), '0'],
            [$wrap(['a' => 1, 'b' => 2]), '1'],
            [$wrap(['a' => 1, 'b' => 2]), '2'],
        ];
    }

    /**
     * @param object $input
     * @param string $key
     * @return void
     * @dataProvider fromObjectTrueDataProvider
     */
    public function testFromObjectTrue(object $input, string $key): void
    {
        // When
        $result = ObjectAccessHelper::hasReadableProperty($input, $key);

        // Then
        $this->assertTrue($result);
    }

    public function fromObjectTrueDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), 'publicProperty'],
            [new ClassWithAccessibleProperties(), 'publicPropertyWithMethodsAccess'],
            [new ClassWithAccessibleProperties(), 'protectedPropertyWithMethodsAccess'],
            [new ClassWithAccessibleProperties(), 'privatePropertyWithMethodsAccess'],
        ];
    }

    /**
     * @param object $input
     * @param string $key
     * @return void
     * @dataProvider fromObjectFalseDataProvider
     */
    public function testFromObjectFalse(object $input, string $key): void
    {
        // When
        $result = ObjectAccessHelper::hasReadableProperty($input, $key);

        // Then
        $this->assertFalse($result);
    }

    public function fromObjectFalseDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), ''],
            [new ClassWithAccessibleProperties(), '0'],
            [new ClassWithAccessibleProperties(), 'unknownProperty'],
            [new ClassWithAccessibleProperties(), 'protectedProperty'],
            [new ClassWithAccessibleProperties(), 'privateProperty'],
        ];
    }
}
