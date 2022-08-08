<?php


namespace Smoren\Schemator\Exceptions;


use Smoren\ExtendedExceptions\BaseException;
use Throwable;

class SchematorException extends BaseException
{
    const FILTER_NOT_FOUND = 1;
    const FILTER_ERROR = 2;
    const CANNOT_GET_VALUE = 3;

    /**
     * @param array $filters
     * @param string $filterName
     * @throws SchematorException
     */
    public static function ensureFilterExists(array $filters, string $filterName): void
    {
        if(!isset($filters[$filterName])) {
            throw new SchematorException(
                "filter '{$filterName}' not found",
                SchematorException::FILTER_NOT_FOUND,
                null,
                [
                    'filter_name' => $filterName,
                ]
            );
        }
    }

    /**
     * @param string $filterName
     * @param $filterConfig
     * @param $source
     * @param Throwable $previous
     * @return SchematorException
     */
    public static function createAsFilterError(
        string $filterName, $filterConfig, $source, Throwable $previous
    ): SchematorException
    {
        return new SchematorException(
            "filter error: '{$filterName}'",
            SchematorException::FILTER_ERROR,
            $previous,
            [
                'error' => $previous->getMessage(),
                'filter_name' => $filterName,
                'config' => $filterConfig,
                'source' => $source,
            ]
        );
    }

    /**
     * @param $key
     * @param $source
     * @param Throwable|null $previous
     * @return SchematorException
     */
    public static function createAsUnknownKey($key, $source, ?Throwable $previous = null): SchematorException
    {
        return new SchematorException(
            "cannot get value by key '{$key}'",
            SchematorException::CANNOT_GET_VALUE,
            $previous,
            [
                'key' => $key,
                'source' => $source,
            ]
        );
    }

    /**
     * @param $key
     * @param Throwable|null $previous
     * @return SchematorException
     */
    public static function createAsNullSource($key, ?Throwable $previous = null): SchematorException
    {
        return new SchematorException(
            "cannot get value from null",
            SchematorException::CANNOT_GET_VALUE,
            $previous,
            [
                'key' => $key,
                'source' => null,
            ]
        );
    }
}
