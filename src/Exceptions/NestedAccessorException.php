<?php

namespace Smoren\Schemator\Exceptions;

use Smoren\ExtendedExceptions\BaseException;

/**
 * Class NestedAccessorException
 * @author Smoren <ofigate@gmail.com>
 */
class NestedAccessorException extends BaseException
{
    public const SOURCE_IS_SCALAR = 1;
    public const CANNOT_GET_VALUE = 2;
    public const CANNOT_SET_VALUE = 3;

    /**
     * Creates a new exception instance for "source is scalar" error
     * @param mixed $source source
     * @return NestedAccessorException
     */
    public static function createAsSourceIsScalar($source): NestedAccessorException
    {
        return new NestedAccessorException(
            'source is scalar',
            NestedAccessorException::SOURCE_IS_SCALAR,
            null,
            [
                'source_type' => gettype($source),
            ]
        );
    }

    /**
     * Creates a new exception instance for "cannot get value" error
     * @param string $key path key
     * @param int $count errors count
     * @return NestedAccessorException
     */
    public static function createAsCannotGetValue(string $key, int $count): NestedAccessorException
    {
        return new NestedAccessorException(
            "cannot get value by key '{$key}'",
            NestedAccessorException::CANNOT_GET_VALUE,
            null,
            [
                'key' => $key,
                'count' => $count,
            ]
        );
    }

    /**
     * Creates a new exception instance for "cannot set value" error
     * @param string $key path key
     * @return NestedAccessorException
     */
    public static function createAsCannotSetValue(string $key): NestedAccessorException
    {
        return new NestedAccessorException(
            "cannot set value by key '{$key}'",
            NestedAccessorException::CANNOT_SET_VALUE,
            null,
            [
                'key' => $key,
            ]
        );
    }
}
