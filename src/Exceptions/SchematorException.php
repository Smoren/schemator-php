<?php

namespace Smoren\Schemator\Exceptions;

use Smoren\Schemator\Interfaces\FilterContextInterface;
use Throwable;

/**
 * Class SchematorException.
 */
class SchematorException extends \Exception
{
    public const UNSUPPORTED_SOURCE_TYPE = 1;
    public const UNSUPPORTED_KEY_TYPE = 2;
    public const UNSUPPORTED_FILTER_CONFIG_TYPE = 3;
    public const FILTER_NOT_FOUND = 4;
    public const FILTER_ERROR = 5;
    public const CANNOT_GET_VALUE = 6;
    public const BAD_FILTER_CONFIG = 7;
    public const BAD_FILTER_SOURCE = 8;

    /**
     * @var array<mixed>|null
     */
    protected ?array $data;

    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param array<mixed>|null $data
     */
    public function __construct(
        string $message,
        int $code,
        Throwable $previous = null,
        ?array $data = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    /**
     * @return array<mixed>
     */
    public function getData(): array
    {
        return $this->data ?? [];
    }

    /**
     * Creates a new exception instance for filter not found error.
     *
     * @param string $filterName name of filter.
     */
    public static function createAsFilterNotFound(string $filterName): SchematorException
    {
        return new SchematorException(
            "filter '{$filterName}' not found",
            SchematorException::FILTER_NOT_FOUND,
            null,
            [
                'filter_name' => $filterName,
            ]
        );
    }

    /**
     * Creates a new exception instance for "cannot get value" error
     * @param mixed $source data to get value from
     * @param string $key path key to get value by
     * @param Throwable|null $previous previous exception
     * @return SchematorException
     */
    public static function createAsCannotGetValue(
        $source,
        string $key,
        ?Throwable $previous = null
    ): SchematorException {
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
     * Creates a new exception instance for "unsupported source type" error
     * @param mixed $source source
     * @param mixed $key path key to get value by
     * @param Throwable|null $previous previous exception
     * @return SchematorException
     */
    public static function createAsUnsupportedSourceType(
        $source,
        $key,
        ?Throwable $previous = null
    ): SchematorException {
        $sourceType = gettype($source);
        return new SchematorException(
            "unsupported source type '{$sourceType}'",
            SchematorException::UNSUPPORTED_SOURCE_TYPE,
            $previous,
            [
                'key' => $key,
                'source' => $source,
                'source_type' => $sourceType,
            ]
        );
    }

    /**
     * Creates a new exception instance for "unsupported key type" error
     * @param mixed $source source
     * @param mixed $key path key to get value by
     * @param Throwable|null $previous previous exception
     * @return SchematorException
     */
    public static function createAsUnsupportedKeyType(
        $source,
        $key,
        ?Throwable $previous = null
    ): SchematorException {
        $keyType = gettype($key);
        return new SchematorException(
            "unsupported key type '{$keyType}'",
            SchematorException::UNSUPPORTED_KEY_TYPE,
            $previous,
            [
                'key' => $key,
                'source' => $source,
                'key_type' => $keyType,
            ]
        );
    }

    /**
     * Creates a new exception instance for "unsupported filter config type" error
     * @param mixed $filterConfig filter config
     * @param Throwable|null $previous previous exception
     * @return SchematorException
     */
    public static function createAsUnsupportedFilterConfigType(
        $filterConfig,
        ?Throwable $previous = null
    ): SchematorException {
        $filterConfigType = gettype($filterConfig);
        return new SchematorException(
            "unsupported filter config type '{$filterConfigType}'",
            SchematorException::UNSUPPORTED_FILTER_CONFIG_TYPE,
            $previous,
            [
                'filter_config' => $filterConfig,
                'filter_config_type' => $filterConfigType,
            ]
        );
    }

    /**
     * Creates a new exception instance for filter execution error
     * @param FilterContextInterface $filterContext name of the filter
     * @param ?Throwable $previous exception thrown in the filter body
     * @return SchematorException
     */
    public static function createAsFilterError(
        FilterContextInterface $filterContext,
        ?Throwable $previous = null
    ): SchematorException {
        return new SchematorException(
            "filter error: '{$filterContext->getFilterName()}'",
            SchematorException::FILTER_ERROR,
            $previous,
            [
                'error' => $previous ? $previous->getMessage() : "filter error: '{$filterContext->getFilterName()}'",
                'filter_name' => $filterContext->getFilterName(),
                'config' => $filterContext->getConfig(),
                'source' => $filterContext->getSource(),
            ]
        );
    }

    /**
     * Creates a new exception instance for filter config error
     * @param FilterContextInterface $filterContext name of the filter
     * @param ?Throwable $previous exception thrown in the filter body
     * @return SchematorException
     */
    public static function createAsBadFilterConfig(
        FilterContextInterface $filterContext,
        ?Throwable $previous = null
    ): SchematorException {
        return new SchematorException(
            "bad config for filter '{$filterContext->getFilterName()}'",
            SchematorException::BAD_FILTER_CONFIG,
            $previous,
            [
                'filter_name' => $filterContext->getFilterName(),
                'config' => $filterContext->getConfig(),
                'source' => $filterContext->getSource(),
            ]
        );
    }

    /**
     * Creates a new exception instance for filter source error
     * @param FilterContextInterface $filterContext name of the filter
     * @param ?Throwable $previous exception thrown in the filter body
     * @return SchematorException
     */
    public static function createAsBadFilterSource(
        FilterContextInterface $filterContext,
        ?Throwable $previous = null
    ): SchematorException {
        return new SchematorException(
            "bad source for filter '{$filterContext->getFilterName()}'",
            SchematorException::BAD_FILTER_SOURCE,
            $previous,
            [
                'filter_name' => $filterContext->getFilterName(),
                'config' => $filterContext->getConfig(),
                'source' => $filterContext->getSource(),
            ]
        );
    }
}
