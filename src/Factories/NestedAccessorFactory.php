<?php

namespace Smoren\Schemator\Factories;

use Smoren\Schemator\Components\NestedAccessor;
use Smoren\Schemator\Interfaces\NestedAccessorFactoryInterface;
use Smoren\Schemator\Interfaces\NestedAccessorInterface;

class NestedAccessorFactory implements NestedAccessorFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public static function create(&$source, string $pathDelimiter = '.'): NestedAccessorInterface
    {
        return new NestedAccessor($source, $pathDelimiter);
    }

    /**
     * {@inheritDoc}
     */
    public static function fromArray(array &$source, string $pathDelimiter = '.'): NestedAccessorInterface
    {
        return static::create($source, $pathDelimiter);
    }

    /**
     * {@inheritDoc}
     */
    public static function fromObject(object &$source, string $pathDelimiter = '.'): NestedAccessorInterface
    {
        return static::create($source, $pathDelimiter);
    }
}
