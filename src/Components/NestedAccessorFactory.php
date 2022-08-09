<?php


namespace Smoren\Schemator\Components;


use Smoren\Schemator\Interfaces\NestedAccessorFactoryInterface;
use Smoren\Schemator\Interfaces\NestedAccessorInterface;

class NestedAccessorFactory implements NestedAccessorFactoryInterface
{
    /**
     * @param array|null $source
     * @param string $pathDelimiter
     * @return NestedAccessorInterface
     */
    public static function create(?array &$source, string $pathDelimiter = '.'): NestedAccessorInterface
    {
        return new NestedAccessor($source, $pathDelimiter);
    }
}
