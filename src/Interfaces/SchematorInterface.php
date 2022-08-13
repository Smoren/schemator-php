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
     * @param array $source input data to convert
     * @param array $schema schema for converting
     * @param bool $strict throw exception if key not exist
     * @return array|mixed converted data
     * @throws SchematorException
     */
    public function exec(array $source, array $schema, bool $strict = false);

    /**
     * Returns value from source by schema item
     * @param array|null $source source to extract data from
     * @param string|array $key item of schema (string as path or array as filter config)
     * @param bool $strict throw exception if key not exist
     * @return mixed result value
     * @throws SchematorException
     */
    public function getValue(?array $source, $key, bool $strict = false);

    /**
     * Adds new filter
     * @param string $filterName filter name
     * @param callable $callback filter callback
     * @return $this
     */
    public function addFilter(string $filterName, callable $callback): self;
}
