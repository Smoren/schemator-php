<?php

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
     * @param bool $strict throw exception if key not exist
     * @return array<string, mixed>|mixed converted data
     * @throws SchematorException
     */
    public function convert($source, array $schema, bool $strict = false);

    /**
     * Converts input data with using schema
     * @param array<string, mixed>|object $source input data to convert
     * @param array<string, mixed> $schema schema for converting
     * @param bool $strict throw exception if key not exist
     * @return array<string, mixed>|mixed converted data
     * @throws SchematorException
     * @deprecated please use convert() method
     * @see SchematorInterface::convert()
     */
    public function exec($source, array $schema, bool $strict = false);

    /**
     * Returns value from source by schema item
     * @param array<string, mixed>|object|mixed|null $source source to extract data from
     * @param string|array<int, mixed>|mixed|null $key item of schema (string as path or array as filter config)
     * @param bool $strict throw exception if key not exist
     * @return mixed result value
     * @throws SchematorException
     */
    public function getValue($source, $key, bool $strict = false);

    /**
     * Adds new filter
     * @param string $filterName filter name
     * @param callable $callback filter callback
     * @return $this
     */
    public function addFilter(string $filterName, callable $callback): self;
}
