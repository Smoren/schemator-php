<?php


namespace Smoren\Schemator\Interfaces;


/**
 * Interface NestedAccessorFactoryInterface
 * @author Smoren <ofigate@gmail.com>
 */
interface NestedAccessorFactoryInterface
{
    /**
     * Creates NestedAccessorInterface instance
     * @param array|null $source source for accessing
     * @param string $pathDelimiter nesting path separator
     */
    public static function create(?array &$source, string $pathDelimiter = '.'): NestedAccessorInterface;
}