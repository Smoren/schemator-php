<?php


namespace Smoren\Schemator\Exceptions;


use Smoren\ExtendedExceptions\BaseException;

class ArrayNestedAccessorException extends BaseException
{
    const KEY_NOT_FOUND = 1;

    /**
     * @param string $key
     * @param array $source
     * @throws ArrayNestedAccessorException
     */
    public static function throwWithKeyNotFound(string $key, array $source): void
    {
        throw new ArrayNestedAccessorException(
            "key '{$key}' not found",
            ArrayNestedAccessorException::KEY_NOT_FOUND,
            null,
            [
                'key' => $key,
                'source' => $source,
            ]
        );
    }
}
