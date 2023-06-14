<?php

namespace Smoren\Schemator\Structs;

use Smoren\Schemator\Exceptions\SchematorException;

/**
 * Class ErrorsLevelMask
 * @author Smoren <ofigate@gmail.com>
 */
class ErrorsLevelMask extends Bitmap
{
    /**
     * @param $value
     * @return ErrorsLevelMask
     */
    public static function create($value): ErrorsLevelMask
    {
        /** @var ErrorsLevelMask $result */
        $result = parent::create($value);
        return $result;
    }

    /**
     * Creates bitmap of all errors
     * @return ErrorsLevelMask
     */
    public static function all(): ErrorsLevelMask
    {
        return static::create([
            SchematorException::FILTER_NOT_FOUND,
            SchematorException::FILTER_ERROR,
            SchematorException::CANNOT_GET_VALUE,
            SchematorException::UNSUPPORTED_SOURCE_TYPE,
            SchematorException::UNSUPPORTED_KEY_TYPE,
            SchematorException::UNSUPPORTED_FILTER_CONFIG_TYPE,
            SchematorException::BAD_FILTER_CONFIG,
            SchematorException::BAD_FILTER_SOURCE,
        ]);
    }

    /**
     * Creates bitmap of default errors
     * @return ErrorsLevelMask
     */
    public static function default(): ErrorsLevelMask
    {
        return static::create([
            SchematorException::FILTER_NOT_FOUND,
            SchematorException::UNSUPPORTED_SOURCE_TYPE,
            SchematorException::UNSUPPORTED_KEY_TYPE,
            SchematorException::UNSUPPORTED_FILTER_CONFIG_TYPE,
            SchematorException::BAD_FILTER_CONFIG,
        ]);
    }

    /**
     * Creates bitmap of no errors
     * @return ErrorsLevelMask
     */
    public static function nothing(): ErrorsLevelMask
    {
        return static::create([]);
    }
}
