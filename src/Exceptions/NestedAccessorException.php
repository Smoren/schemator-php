<?php


namespace Smoren\Schemator\Exceptions;


use Smoren\ExtendedExceptions\BaseException;

/**
 * Class NestedAccessorException
 * @author Smoren <ofigate@gmail.com>
 */
class NestedAccessorException extends BaseException
{
    const SOURCE_IS_SCALAR = 1;
    const KEY_NOT_FOUND = 2;

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
     * Creates a new exception instance for "not found key" error
     * @param string $key path key
     * @param int $count errors count
     * @return NestedAccessorException
     */
    public static function createAsKeyNotFound(string $key, int $count): NestedAccessorException
    {
        return new NestedAccessorException(
            "key '{$key}' not found",
            NestedAccessorException::KEY_NOT_FOUND,
            null,
            [
                'key' => $key,
                'count' => $count,
            ]
        );
    }
}
