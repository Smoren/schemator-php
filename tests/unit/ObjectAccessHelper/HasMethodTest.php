<?php

declare(strict_types=1);

namespace Smoren\Schemator\Tests\Unit\ObjectAccessHelper;

use Codeception\Test\Unit;
use Smoren\Schemator\Helpers\ObjectAccessHelper;
use Smoren\Schemator\Tests\Unit\Fixtures\ClassWithAccessibleProperties;

class HasMethodTest extends Unit
{
    /**
     * @param object $input
     * @param string $key
     * @return void
     * @dataProvider fromObjectTrueDataProvider
     */
    public function testFromObjectTrue(object $input, string $key): void
    {
        // When
        $result = ObjectAccessHelper::hasMethod($input, $key);

        // Then
        $this->assertTrue($result);
    }

    public function fromObjectTrueDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), 'getPublicPropertyWithMethodsAccess'],
            [new ClassWithAccessibleProperties(), 'setPublicPropertyWithMethodsAccess'],
            [new ClassWithAccessibleProperties(), 'getProtectedPropertyWithMethodsAccess'],
            [new ClassWithAccessibleProperties(), 'setProtectedPropertyWithMethodsAccess'],
            [new ClassWithAccessibleProperties(), 'getPrivatePropertyWithMethodsAccess'],
            [new ClassWithAccessibleProperties(), 'setPrivatePropertyWithMethodsAccess'],
            [new ClassWithAccessibleProperties(), 'privateMethod'],
            [new ClassWithAccessibleProperties(), 'privateMethod'],
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
        $result = ObjectAccessHelper::hasMethod($input, $key);

        // Then
        $this->assertFalse($result);
    }

    public function fromObjectFalseDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), 'unknownMethod'],
        ];
    }
}
