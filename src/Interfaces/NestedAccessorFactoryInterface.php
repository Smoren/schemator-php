<?php

declare(strict_types=1);

namespace Smoren\Schemator\Interfaces;

interface NestedAccessorFactoryInterface
{
    /**
     * Creates NestedAccessorInterface instance.
     *
     * @param array<int|string, mixed>|object|null $source source for accessing
     * @param non-empty-string $pathDelimiter nesting path separator
     *
     * @return NestedAccessorInterface<string|string[]|null>
     */
    public static function create(&$source, string $pathDelimiter = '.'): NestedAccessorInterface;

    /**
     * Creates NestedAccessorInterface instance.
     *
     * @param array<mixed> $source source for accessing
     * @param non-empty-string $pathDelimiter nesting path separator
     *
     * @return NestedAccessorInterface<string|string[]|null>
     */
    public static function fromArray(array &$source, string $pathDelimiter = '.'): NestedAccessorInterface;

    /**
     * Creates NestedAccessorInterface instance.
     *
     * @param object $source source for accessing
     * @param non-empty-string $pathDelimiter nesting path separator
     *
     * @return NestedAccessorInterface<string|string[]|null>
     */
    public static function fromObject(object &$source, string $pathDelimiter = '.'): NestedAccessorInterface;
}
