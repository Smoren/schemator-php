<?php


namespace Smoren\Schemator\Exceptions;


use Smoren\ExtendedExceptions\BaseException;

class NestedAccessorException extends BaseException
{
    const SOURCE_IS_NOT_ACCESSIBLE = 1;
    const KEY_NOT_FOUND = 2;

    /**
     * @param mixed $source
     * @return NestedAccessorException
     */
    public static function createAsSourceIsNotAccessible($source): NestedAccessorException
    {
        return new NestedAccessorException(
            'source is not accessible',
            NestedAccessorException::SOURCE_IS_NOT_ACCESSIBLE,
            null,
            [
                'source_type' => gettype($source),
            ]
        );
    }

    /**
     * @param string $key
     * @param int $count
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
