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
     * @param array<int|string, mixed>|object|null $source source for accessing
     * @param non-empty-string $pathDelimiter nesting path separator
     */
    public static function create(&$source, string $pathDelimiter = '.'): NestedAccessorInterface;
}
