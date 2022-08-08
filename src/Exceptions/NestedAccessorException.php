<?php


namespace Smoren\Schemator\Exceptions;


use Smoren\ExtendedExceptions\BaseException;

class NestedAccessorException extends BaseException
{
    const KEY_NOT_FOUND = 1;

    /**
     * @param string $key
     * @param array $source
     * @return NestedAccessorException
     */
    public static function createAsKeyNotFound(string $key, array $source): NestedAccessorException
    {
        return new NestedAccessorException(
            "key '{$key}' not found",
            NestedAccessorException::KEY_NOT_FOUND,
            null,
            [
                'key' => $key,
                'source' => $source,
            ]
        );
    }
}
