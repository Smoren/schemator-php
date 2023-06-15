<?php

declare(strict_types=1);

namespace Smoren\Schemator\Tests\Unit\ObjectAccessHelper;

use Codeception\Test\Unit;
use Smoren\Schemator\Helpers\ObjectAccessHelper;
use Smoren\Schemator\Tests\Unit\Fixtures\ClassWithAccessibleProperties;
use stdClass;

class GetPropertyValueTest extends Unit
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
        $result = ObjectAccessHelper::getPropertyValue($input, $key);

        // Then
        $this->assertSame($expected, $result);
    }

    public function fromStdClassSuccessDataProvider(): array
    {
        $wrap = static function(array $input): object {
            return (object)$input;
        };

        return [
            [$wrap(['a' => 1, 'b' => 2]), 'a', 1],
            [$wrap(['a' => 1, 'b' => 2]), 'b', 2],
        ];
    }

    /**
     * @param stdClass $input
     * @param string $key
     * @return void
     * @dataProvider fromStdClassFailDataProvider
     */
    public function testFromStdClassFail(stdClass $input, string $key): void
    {
        try {
            // When
            ObjectAccessHelper::getPropertyValue($input, $key);
            $this->fail();
        } catch(\InvalidArgumentException $e) {
            // Then
            $this->assertSame("Property '".get_class($input)."::{$key}' is not readable", $e->getMessage());
        }
    }

    public function fromStdClassFailDataProvider(): array
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
     * @param mixed $expected
     * @return void
     * @dataProvider fromObjectSuccessDataProvider
     */
    public function testFromObjectSuccess(object $input, string $key, $expected): void
    {
        // When
        $result = ObjectAccessHelper::getPropertyValue($input, $key);

        // Then
        $this->assertSame($expected, $result);
    }

    public function fromObjectSuccessDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), 'publicProperty', 1],
            [new ClassWithAccessibleProperties(), 'publicProperty', 1],
            [new ClassWithAccessibleProperties(), 'publicPropertyWithGetterAccess', 2],
            [new ClassWithAccessibleProperties(), 'publicPropertyWithGetterAccess', 2],
            [new ClassWithAccessibleProperties(), 'protectedPropertyWithGetterAccess', 4],
            [new ClassWithAccessibleProperties(), 'protectedPropertyWithGetterAccess', 4],
            [new ClassWithAccessibleProperties(), 'privatePropertyWithGetterAccess', 6],
            [new ClassWithAccessibleProperties(), 'privatePropertyWithGetterAccess', 6],
        ];
    }

    /**
     * @param object $input
     * @param string $key
     * @return void
     * @dataProvider fromObjectFailDataProvider
     */
    public function testFromObjectFail(object $input, string $key): void
    {
        try {
            // When
            ObjectAccessHelper::getPropertyValue($input, $key);
            $this->fail();
        } catch(\InvalidArgumentException $e) {
            // Then
            $this->assertSame("Property '".get_class($input)."::{$key}' is not readable", $e->getMessage());
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
}
