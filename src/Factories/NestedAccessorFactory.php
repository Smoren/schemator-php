<?php

namespace Smoren\Schemator\Factories;

use Smoren\Schemator\Interfaces\NestedAccessorFactoryInterface;
use Smoren\Schemator\Components\NestedAccessor;
use Smoren\Schemator\Exceptions\NestedAccessorException;

/**
 * Class NestedAccessorFactory
 * @author Smoren <ofigate@gmail.com>
 */
class NestedAccessorFactory implements NestedAccessorFactoryInterface
{
    /**
     * @inheritDoc
     * @throws NestedAccessorException
     */
    public static function create(&$source, string $pathDelimiter = '.'): NestedAccessor
    {
        return new NestedAccessor($source, $pathDelimiter);
    }
}
