<?php


namespace Smoren\Schemator\Interfaces;


use Smoren\Schemator\Exceptions\NestedAccessorException;

/**
 * Interface NestedAccessorInterface
 * @author Smoren <ofigate@gmail.com>
 */
interface NestedAccessorInterface
{
    /**
     * Getter of source value by nested path
     * @param string $path nested path
     * @param bool $strict when true throw exception if path not exist in source data
     * @return mixed value got by path
     * @throws NestedAccessorException
     */
    public function get(string $path, bool $strict = true);

    /**
     * Setter for saving value to source by nested path
     * @param string $path nested path
     * @param mixed $value value to save to source by path
     * @param bool $strict when true throw exception if path not exist in source object
     * @return mixed value got by path
     */
    public function set(string $path, $value, bool $strict = true);
}