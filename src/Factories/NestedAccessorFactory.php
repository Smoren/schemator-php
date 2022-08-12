<?php


namespace Smoren\Schemator\Factories;


use Smoren\Schemator\Components\NestedAccessor;
use Smoren\Schemator\Exceptions\NestedAccessorException;
use Smoren\Schemator\Interfaces\NestedAccessorFactoryInterface;

class NestedAccessorFactory implements NestedAccessorFactoryInterface
{
    /**
     * @param array|null $source
     * @param string $pathDelimiter
     * @return NestedAccessor
     * @throws NestedAccessorException
     */
    public static function create(?array &$source, string $pathDelimiter = '.'): NestedAccessor
    {
        return new NestedAccessor($source, $pathDelimiter);
    }
}
