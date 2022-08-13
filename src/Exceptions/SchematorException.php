<?php


namespace Smoren\Schemator\Exceptions;


use Smoren\ExtendedExceptions\BaseException;
use Throwable;

/**
 * Class SchematorException
 * @author Smoren <ofigate@gmail.com>
 */
class SchematorException extends BaseException
{
    const FILTER_NOT_FOUND = 1;
    const FILTER_ERROR = 2;
    const CANNOT_GET_VALUE = 3;

    /**
     * Checks that filter exists in map
     * @param array $filterMap filters mapped by name
     * @param string $filterName name of filter
     * @throws SchematorException
     */
    public static function ensureFilterExists(array $filterMap, string $filterName): void
    {
        if(!isset($filterMap[$filterName])) {
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
     * Creates a new exception instance for filter execution error
     * @param string $filterName name of the filter
     * @param mixed $filterConfig arguments for filter
     * @param mixed $source source for filtering
     * @param Throwable $previous exception thrown in the filter body
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
     * Creates a new exception instance for "cannot get value" error
     * @param string $key path key to get value by
     * @param mixed $source data to get value from
     * @param Throwable|null $previous previous exception
     * @return SchematorException
     */
    public static function createAsCannotGetValue(string $key, $source, ?Throwable $previous = null): SchematorException
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
     * Creates a new exception instance for "source is null" error
     * @param mixed $key path key to get value by
     * @param Throwable|null $previous previous exception
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
