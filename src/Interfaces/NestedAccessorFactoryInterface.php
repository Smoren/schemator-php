<?php

namespace Smoren\Schemator\Interfaces;

/**
 * @template TPath of string|string[]|null
 */
interface NestedAccessorFactoryInterface
{
    /**
     * Creates NestedAccessorInterface instance.
     *
     * @param array<int|string, mixed>|object|null $source source for accessing
     * @param non-empty-string $pathDelimiter nesting path separator
     *
     * @return NestedAccessorInterface<TPath>
     */
    public static function create(&$source, string $pathDelimiter = '.'): NestedAccessorInterface;

    /**
     * Creates NestedAccessorInterface instance.
     *
     * @param array<mixed> $source source for accessing
     * @param non-empty-string $pathDelimiter nesting path separator
     *
     * @return NestedAccessorInterface<TPath>
     */
    public static function fromArray(array &$source, string $pathDelimiter = '.'): NestedAccessorInterface;

    /**
     * Creates NestedAccessorInterface instance.
     *
     * @param object $source source for accessing
     * @param non-empty-string $pathDelimiter nesting path separator
     *
     * @return NestedAccessorInterface<TPath>
     */
    public static function fromObject(object &$source, string $pathDelimiter = '.'): NestedAccessorInterface;
}
