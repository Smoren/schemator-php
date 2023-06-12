<?php

declare(strict_types=1);

namespace Smoren\Schemator\Helpers;

use ArrayAccess;
use stdClass;

/**
 * Tool for map-like accessing of different containers by string keys.
 *
 * Can access:
 *  - properties of objects (by name or by getter);
 *  - elements of arrays and ArrayAccess objects (by key).
 *
 * @template TKey of string|int
 * @template TValue of mixed
 */
class ContainerAccessHelper
{
    /**
     * Returns value from the container by key or default value if key does not exist or not accessible.
     *
     * @param array<TKey, TValue>|ArrayAccess<TKey, TValue>|object|mixed $container
     * @param TKey $key
     * @param TValue|null $defaultValue
     *
     * @return TValue|null
     *
     * @throws \InvalidArgumentException
     */
    public static function get($container, $key, $defaultValue = null)
    {
        switch (true) {
            case is_array($container):
                return static::getFromArray($container, $key, $defaultValue);
            case $container instanceof ArrayAccess:
                return static::getFromArrayAccess($container, $key, $defaultValue);
            case is_object($container):
                return static::getFromObject($container, $key, $defaultValue);
        }

        return $defaultValue;
    }

    /**
     * Returns value from the container by key (sets and returns default value if key does not exist).
     *
     * @param array<TKey, TValue>|ArrayAccess<TKey, TValue>|object|mixed $container
     * @param TKey $key
     * @param TValue|null $defaultValue
     *
     * @return TValue|null
     *
     * @throws \InvalidArgumentException
     */
    public static function &getRef(&$container, $key, $defaultValue = null)
    {
        switch (true) {
            case is_array($container):
                return static::getRefFromArray($container, $key, $defaultValue);
            case $container instanceof ArrayAccess:
                return static::getRefFromArrayAccess($container, $key, $defaultValue);
            case is_object($container):
                return static::getRefFromObject($container, $key, $defaultValue);
        }

        return $defaultValue;
    }

    /**
     * Sets value to the container by key.
     *
     * @param array<TKey, TValue>|ArrayAccess<TKey, TValue>|object $container
     * @param TKey $key
     * @param TValue $value
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function set(&$container, $key, $value): void
    {
        switch (true) {
            case is_array($container):
            case $container instanceof ArrayAccess:
                $container[$key] = $value;
                break;
            case is_object($container):
                static::setToObject($container, $key, $value);
                break;
            default:
                $type = gettype($container);
                throw new \InvalidArgumentException("Cannot set value to variable of type '{$type}'");
        }
    }

    /**
     * Deletes key from the container.
     *
     * @param array<TKey, TValue>|ArrayAccess<TKey, TValue>|object $container
     * @param TKey $key
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function delete(&$container, $key): void
    {
        switch (true) {
            case is_array($container):
            case $container instanceof ArrayAccess:
                unset($container[$key]);
                break;
            case $container instanceof stdClass:
                unset($container->{$key});
                break;
            default:
                $type = gettype($container);
                throw new \InvalidArgumentException("Cannot delete key from variable of type '{$type}'");
        }
    }

    /**
     * Returns true if the accessible key exists in the container.
     *
     * @param array<TKey, TValue>|ArrayAccess<TKey, TValue>|object|mixed $container
     * @param TKey $key
     *
     * @return bool
     */
    public static function exists($container, $key): bool
    {
        switch (true) {
            case is_array($container):
                return static::existsInArray($container, $key);
            case $container instanceof ArrayAccess:
                return static::existsInArrayAccess($container, $key);
            case is_object($container):
                return static::existsInObject($container, $key);
        }
        return false;
    }

    /**
     * @param mixed $container
     * @return bool
     */
    public static function isArrayAccessible($container): bool
    {
        return is_array($container) || ($container instanceof ArrayAccess);
    }

    /**
     * Returns value from the array by key or default value if key does not exist.
     *
     * @param array<TKey, TValue> $container
     * @param TKey $key
     * @param TValue|null $defaultValue
     *
     * @return TValue|null
     */
    protected static function getFromArray(array $container, $key, $defaultValue)
    {
        if (static::existsInArray($container, $key)) {
            return $container[$key];
        }

        return $defaultValue ?? null;
    }

    /**
     * Returns reference to value from the array by key (sets and returns default value if key does not exist).
     *
     * @param array<TKey, TValue> $container
     * @param TKey $key
     * @param TValue|null $defaultValue
     *
     * @return TValue|null
     */
    protected static function &getRefFromArray(array &$container, $key, $defaultValue)
    {
        if (!static::existsInArray($container, $key)) {
            $container[$key] = $defaultValue;
        }

        return $container[$key];
    }

    /**
     * Returns true if the key exists in the array.
     *
     * @param array<TKey, TValue> $container
     * @param TKey $key
     *
     * @return bool
     */
    protected static function existsInArray(array $container, $key): bool
    {
        return array_key_exists($key, $container);
    }

    /**
     * Returns value from the ArrayAccess object by key or default value if key does not exist.
     *
     * @param ArrayAccess<TKey, TValue> $container
     * @param TKey $key
     * @param TValue|null $defaultValue
     *
     * @return TValue|null
     */
    protected static function getFromArrayAccess(ArrayAccess $container, $key, $defaultValue)
    {
        if (static::existsInArrayAccess($container, $key)) {
            return $container[$key];
        }

        return $defaultValue ?? null;
    }

    /**
     * Returns reference to value from the ArrayAccess object by key
     * (sets and returns default value if key does not exist).
     *
     * @param ArrayAccess<TKey, TValue> $container
     * @param TKey $key
     * @param TValue|null $defaultValue
     *
     * @return TValue|null
     */
    protected static function &getRefFromArrayAccess(ArrayAccess &$container, $key, $defaultValue)
    {
        if (!static::existsInArrayAccess($container, $key)) {
            /** @var TValue $defaultValue */
            $container[$key] = $defaultValue;
        }

        return $container[$key];
    }

    /**
     * Returns true if the key exists in the ArrayAccess object.
     *
     * @param ArrayAccess<TKey, TValue> $container
     * @param TKey $key
     *
     * @return bool
     */
    protected static function existsInArrayAccess(ArrayAccess $container, $key): bool
    {
        return $container->offsetExists($key);
    }

    /**
     * Returns value from the object by key or default value if key does not exist.
     *
     * @param object $container
     * @param TKey $key
     * @param TValue|null $defaultValue
     *
     * @return TValue|null
     *
     * @throws \InvalidArgumentException
     */
    protected static function getFromObject(object $container, $key, $defaultValue)
    {
        if (ObjectAccessHelper::hasReadableProperty($container, strval($key))) {
            return ObjectAccessHelper::getPropertyValue($container, strval($key));
        }

        return $defaultValue;
    }

    /**
     * Returns value from the object by key or default value if key does not exist.
     *
     * @param object $container
     * @param TKey $key
     * @param TValue|null $defaultValue
     *
     * @return TValue|null
     *
     * @throws \InvalidArgumentException
     */
    protected static function &getRefFromObject(object &$container, $key, $defaultValue)
    {
        return ObjectAccessHelper::getPropertyRef($container, strval($key), $defaultValue);
    }

    /**
     * Sets property value to the object if it is writable by name or by setter.
     *
     * @param object $container
     * @param TKey $key
     * @param TValue $value
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected static function setToObject(object $container, $key, $value): void
    {
        if (!ObjectAccessHelper::hasWritableProperty($container, strval($key)) && !($container instanceof stdClass)) {
            $className = get_class($container);
            throw new \InvalidArgumentException("Property '{$className}::{$key}' is not writable");
        }

        ObjectAccessHelper::setPropertyValue($container, strval($key), $value);
    }

    /**
     * Returns true if the key exists in the object.
     *
     * @param object $container
     * @param TKey $key
     *
     * @return bool
     */
    protected static function existsInObject(object $container, $key): bool
    {
        return ObjectAccessHelper::hasReadableProperty($container, strval($key));
    }
}
