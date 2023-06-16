<?php

declare(strict_types=1);

namespace Smoren\Schemator\Helpers;

use ReflectionMethod;
use ReflectionProperty;
use Smoren\Schemator\Interfaces\ProxyInterface;
use Smoren\Schemator\Structs\ObjectPropertyProxy;
use stdClass;

/**
 * Tool for reflecting and accessing object properties and methods.
 */
class ObjectAccessHelper
{
    /**
     * Returns value of the object property.
     *
     * Can access property by its name or by getter.
     *
     * @param object $object
     * @param string $propertyName
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public static function getPropertyValue(object $object, string $propertyName)
    {
        if (static::hasPropertyAccessibleByGetter($object, $propertyName)) {
            return static::getPropertyValueByGetter($object, $propertyName);
        }

        if (static::hasPublicProperty($object, $propertyName)) {
            return $object->{$propertyName};
        }

        $className = get_class($object);
        throw new \InvalidArgumentException("Property '{$className}::{$propertyName}' is not readable");
    }

    /**
     * Returns reference to the object property.
     *
     * @param object $object
     * @param string $propertyName
     * @param mixed $defaultValue
     *
     * @return mixed|ProxyInterface<object>
     *
     * @throws \InvalidArgumentException
     */
    public static function &getPropertyRef(object &$object, string $propertyName, $defaultValue = null)
    {
        if (static::hasPublicProperty($object, $propertyName)) {
            return $object->{$propertyName};
        }

        if ($object instanceof stdClass) {
            $object->{$propertyName} = $defaultValue;
            return $object->{$propertyName};
        }

        $proxy = new ObjectPropertyProxy($object, $propertyName);
        return $proxy;
    }

    /**
     * Returns value of the object property.
     *
     * Can access property by its name or by getter.
     *
     * @param object $object
     * @param string $propertyName
     * @param mixed $value
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function setPropertyValue(object $object, string $propertyName, $value): void
    {
        if (static::hasPropertyAccessibleBySetter($object, $propertyName)) {
            static::setPropertyValueBySetter($object, $propertyName, $value);
            return;
        }

        if (static::hasPublicProperty($object, $propertyName) || $object instanceof stdClass) {
            $object->{$propertyName} = $value;
            return;
        }

        $className = get_class($object);
        throw new \InvalidArgumentException("Property '{$className}::{$propertyName}' is not writable");
    }

    /**
     * Returns true if object has property that is accessible to read by name or by getter.
     *
     * @param object $object
     * @param string $propertyName
     *
     * @return bool
     */
    public static function hasReadableProperty(object $object, string $propertyName): bool
    {
        return static::hasPublicProperty($object, $propertyName)
            || static::hasPropertyAccessibleByGetter($object, $propertyName);
    }

    /**
     * Returns true if object has property that is accessible to write by name or by setter.
     *
     * @param object $object
     * @param string $propertyName
     *
     * @return bool
     */
    public static function hasWritableProperty(object $object, string $propertyName): bool
    {
        return static::hasPublicProperty($object, $propertyName)
            || static::hasPropertyAccessibleBySetter($object, $propertyName);
    }

    /**
     * Returns true if object has public property.
     *
     * @param object $object
     * @param string $propertyName
     *
     * @return bool
     */
    public static function hasPublicProperty(object $object, string $propertyName): bool
    {
        if ($object instanceof stdClass) {
            return static::hasProperty($object, $propertyName);
        }

        return
            static::hasProperty($object, $propertyName) &&
            static::getReflectionProperty($object, $propertyName)->isPublic();
    }

    /**
     * Returns true if object has property.
     *
     * @param object $object
     * @param string $propertyName
     *
     * @return bool
     */
    public static function hasProperty(object $object, string $propertyName): bool
    {
        return property_exists($object, $propertyName);
    }

    /**
     * Returns true if object has public method.
     *
     * @param object $object
     * @param string $methodName
     *
     * @return bool
     */
    public static function hasPublicMethod(object $object, string $methodName): bool
    {
        return
            static::hasMethod($object, $methodName) &&
            static::getReflectionMethod($object, $methodName)->isPublic();
    }

    /**
     * Returns true if object has method.
     *
     * @param object $object
     * @param string $methodName
     *
     * @return bool
     */
    public static function hasMethod(object $object, string $methodName): bool
    {
        return method_exists($object, $methodName);
    }

    /**
     * Returns true if object has property that is accessible by getter.
     *
     * @param object $object
     * @param string $propertyName
     *
     * @return bool
     */
    protected static function hasPropertyAccessibleByGetter(object $object, string $propertyName): bool
    {
        return static::hasPublicMethod($object, static::getPropertyGetterName($propertyName));
    }

    /**
     * Returns true if object has property that is accessible by setter.
     *
     * @param object $object
     * @param string $propertyName
     *
     * @return bool
     */
    protected static function hasPropertyAccessibleBySetter(object $object, string $propertyName): bool
    {
        return static::hasPublicMethod($object, static::getPropertySetterName($propertyName));
    }

    /**
     * Returns property value by getter.
     *
     * @param object $object
     * @param string $propertyName
     *
     * @return mixed
     */
    protected static function getPropertyValueByGetter(object $object, string $propertyName)
    {
        return $object->{static::getPropertyGetterName($propertyName)}();
    }

    /**
     * Sets property value by setter.
     *
     * @param object $object
     * @param string $propertyName
     * @param mixed $value
     *
     * @return void
     */
    protected static function setPropertyValueBySetter(object $object, string $propertyName, $value): void
    {
        $object->{static::getPropertySetterName($propertyName)}($value);
    }

    /**
     * Returns reflection object of the object property.
     *
     * @param object $object
     * @param string $propertyName
     *
     * @return ReflectionProperty
     */
    protected static function getReflectionProperty(object $object, string $propertyName): ReflectionProperty
    {
        return new ReflectionProperty(get_class($object), $propertyName);
    }

    /**
     * Returns reflection object of the object method.
     *
     * @param object $object
     * @param string $methodName
     *
     * @return ReflectionMethod
     */
    protected static function getReflectionMethod(object $object, string $methodName): ReflectionMethod
    {
        return new ReflectionMethod(get_class($object), $methodName);
    }

    /**
     * Returns property getter name.
     *
     * @param string $propertyName
     *
     * @return string
     */
    protected static function getPropertyGetterName(string $propertyName): string
    {
        return 'get' . ucfirst($propertyName);
    }

    /**
     * Returns property setter name.
     *
     * @param string $propertyName
     *
     * @return string
     */
    protected static function getPropertySetterName(string $propertyName): string
    {
        return 'set' . ucfirst($propertyName);
    }
}
