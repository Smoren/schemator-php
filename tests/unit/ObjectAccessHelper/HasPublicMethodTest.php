<?php

namespace Smoren\Schemator\Tests\Unit\ObjectAccessHelper;

use Codeception\Test\Unit;
use Smoren\Schemator\Helpers\ObjectAccessHelper;
use Smoren\Schemator\Tests\Unit\Fixtures\ClassWithAccessibleProperties;

class HasPublicMethodTest extends Unit
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
        $result = ObjectAccessHelper::hasPublicMethod($input, $key);

        // Then
        $this->assertTrue($result);
    }

    public function fromObjectTrueDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), 'getPublicPropertyWithGetterAccess'],
            [new ClassWithAccessibleProperties(), 'setPublicPropertyWithGetterAccess'],
            [new ClassWithAccessibleProperties(), 'getProtectedPropertyWithGetterAccess'],
            [new ClassWithAccessibleProperties(), 'setProtectedPropertyWithGetterAccess'],
            [new ClassWithAccessibleProperties(), 'getPrivatePropertyWithGetterAccess'],
            [new ClassWithAccessibleProperties(), 'setPrivatePropertyWithGetterAccess'],
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
        $result = ObjectAccessHelper::hasPublicMethod($input, $key);

        // Then
        $this->assertFalse($result);
    }

    public function fromObjectFalseDataProvider(): array
    {
        return [
            [new ClassWithAccessibleProperties(), 'unknownMethod'],
            [new ClassWithAccessibleProperties(), 'protectedMethod'],
            [new ClassWithAccessibleProperties(), 'privateMethod'],
        ];
    }
}