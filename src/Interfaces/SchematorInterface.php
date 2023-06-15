<?php

declare(strict_types=1);

namespace Smoren\Schemator\Interfaces;

use Smoren\Schemator\Exceptions\SchematorException;

/**
 * Interface SchematorInterface
 * @author Smoren <ofigate@gmail.com>
 */
interface SchematorInterface
{
    /**
     * Converts input data with using schema
     * @param array<string, mixed>|object $source input data to convert
     * @param array<string, mixed> $schema schema for converting
     * @return array<string, mixed>|mixed converted data
     * @throws SchematorException
     */
    public function convert($source, array $schema);

    /**
     * Returns value from source by schema item
     * @param array<string, mixed>|object|mixed|null $source source to extract data from
     * @param string|array<int, mixed>|mixed|null $key item of schema (string as path or array as filter config)
     * @return mixed result value
     * @throws SchematorException
     */
    public function getValue($source, $key);

    /**
     * Setter for pathDelimiter property
     * @param non-empty-string $value new value
     * @return void
     */
    public function setPathDelimiter(string $value): void;

    /**
     * Setter for errorsLevelMask property
     * @param BitmapInterface $value new value
     * @return void
     */
    public function setErrorsLevelMask(BitmapInterface $value): void;

    /**
     * Adds new filter
     * @param string $filterName filter name
     * @param callable $callback filter callback
     * @return $this
     */
    public function addFilter(string $filterName, callable $callback): self;
}
