<?php

namespace Smoren\Schemator\Structs;

use Smoren\Schemator\Helpers\ObjectAccessHelper;
use Smoren\Schemator\Interfaces\ProxyInterface;

/**
 * @template T of object
 * @implements ProxyInterface<T>
 */
class ObjectPropertyProxy implements ProxyInterface
{
    /**
     * @var T
     */
    protected object $object;
    protected string $propertyName;

    /**
     * @param T $object
     * @param string $propertyName
     */
    public function __construct(object $object, string $propertyName)
    {
        $this->object = $object;
        $this->propertyName = $propertyName;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if (!ObjectAccessHelper::hasReadableProperty($this->object, $this->propertyName)) {
            $className = get_class($this->object);
            throw new \BadMethodCallException("Property '{$className}::{$this->propertyName}' is not readable");
        }
        return ObjectAccessHelper::getPropertyValue($this->object, $this->propertyName);
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function setValue($value): void
    {
        if (!ObjectAccessHelper::hasWritableProperty($this->object, $this->propertyName)) {
            $className = get_class($this->object);
            throw new \BadMethodCallException("Property '{$className}::{$this->propertyName}' is not writable");
        }
        ObjectAccessHelper::setPropertyValue($this->object, $this->propertyName, $value);
    }
}
