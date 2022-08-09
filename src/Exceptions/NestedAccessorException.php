<?php


namespace Smoren\Schemator\Exceptions;


use Smoren\ExtendedExceptions\BaseException;

class NestedAccessorException extends BaseException
{
    const KEYS_NOT_FOUND = 1;

    /**
     * @param array $keys
     * @return NestedAccessorException
     */
    public static function createAsKeysNotFound(array $keys): NestedAccessorException
    {
        return new NestedAccessorException(
            'keys ('.implode(', ', $keys).') not found',
            NestedAccessorException::KEYS_NOT_FOUND,
            null,
            [
                'keys' => $keys,
            ]
        );
    }
}
