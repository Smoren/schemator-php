<?php

namespace Smoren\Schemator\Factories;

use Smoren\Schemator\Components\NestedAccessor;
use Smoren\Schemator\Exceptions\NestedAccessorException;
use Smoren\Schemator\Interfaces\NestedAccessorFactoryInterface;

/**
 * Class NestedAccessorFactory
 * @author Smoren <ofigate@gmail.com>
 */
class NestedAccessorFactory implements NestedAccessorFactoryInterface
{
    /**
     * Creates NestedAccessor instance
     * @param array|null $source source for accessing
     * @param string $pathDelimiter nesting path separator
     * @return NestedAccessor nested accessor instance
     * @throws NestedAccessorException
     */
    public static function create(?array &$source, string $pathDelimiter = '.'): NestedAccessor
    {
        return new NestedAccessor($source, $pathDelimiter);
    }
}
