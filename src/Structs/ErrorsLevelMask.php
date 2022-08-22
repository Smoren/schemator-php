<?php

namespace Smoren\Schemator\Structs;

use Smoren\BitmapTools\Interfaces\BitmapInterface;
use Smoren\BitmapTools\Models\Bitmap;
use Smoren\Schemator\Exceptions\SchematorException;

/**
 * Class ErrorsLevelMask
 * @author Smoren <ofigate@gmail.com>
 */
class ErrorsLevelMask extends Bitmap
{
    /**
     * Creates bitmap of all errors
     * @return BitmapInterface
     */
    public static function all(): BitmapInterface
    {
        return new static(static::create([
            SchematorException::FILTER_NOT_FOUND,
            SchematorException::FILTER_ERROR,
            SchematorException::CANNOT_GET_VALUE,
            SchematorException::UNSUPPORTED_SOURCE_TYPE,
            SchematorException::UNSUPPORTED_KEY_TYPE,
            SchematorException::UNSUPPORTED_FILTER_CONFIG_TYPE,
            SchematorException::BAD_FILTER_CONFIG,
            SchematorException::BAD_FILTER_SOURCE,
        ])->getValue());
    }

    /**
     * Creates bitmap of default errors
     * @return BitmapInterface
     */
    public static function default(): BitmapInterface
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
     * @return BitmapInterface
     */
    public static function nothing(): BitmapInterface
    {
        return static::create([]);
    }
}
