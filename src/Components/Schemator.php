<?php

namespace Smoren\Schemator\Components;

use Smoren\BitmapTools\Helpers\BitmapHelper;
use Smoren\Schemator\Interfaces\NestedAccessorFactoryInterface;
use Smoren\Schemator\Interfaces\SchematorInterface;
use Smoren\Schemator\Factories\NestedAccessorFactory;
use Smoren\Schemator\Structs\FilterContext;
use Smoren\Schemator\Exceptions\NestedAccessorException;
use Smoren\Schemator\Exceptions\SchematorException;
use Throwable;

/**
 * Class for schematic data converting
 * @author Smoren <ofigate@gmail.com>
 */
class Schemator implements SchematorInterface
{
    public const ERRORS_LEVEL_DEFAULT = 114;

    /**
     * @var array<string, callable> filters map
     */
    protected array $filterMap = [];
    /**
     * @var non-empty-string delimiter for multilevel paths
     */
    protected string $pathDelimiter;
    /**
     * @var int bitmap errors level mask
     */
    protected int $errorsLevelMask;
    /**
     * @var NestedAccessorFactoryInterface nested accessor factory
     */
    protected NestedAccessorFactoryInterface $nestedAccessorFactory;

    /**
     * Creates bitmap errors level mask
     * @param array<int> $errorCodes
     * @return int
     */
    public static function createErrorsLevelMask(array $errorCodes): int
    {
        return BitmapHelper::create($errorCodes);
    }

    /**
     * Schemator constructor.
     * @param non-empty-string $pathDelimiter delimiter for multilevel paths
     * @param int $errorsLevelMask bitmap errors level mask
     */
    public function __construct(
        string $pathDelimiter = '.',
        int $errorsLevelMask = self::ERRORS_LEVEL_DEFAULT,
        NestedAccessorFactoryInterface $nestedAccessorFactory = null
    ) {
        $this->pathDelimiter = $pathDelimiter;
        $this->errorsLevelMask = $errorsLevelMask;
        $this->nestedAccessorFactory = $nestedAccessorFactory ?? new NestedAccessorFactory();
    }

    /**
     * @inheritDoc
     */
    public function convert($source, array $schema)
    {
        $toAccessor = $this->nestedAccessorFactory->create($result, $this->pathDelimiter);

        foreach($schema as $keyTo => $keyFrom) {
            $value = $this->getValue($source, $keyFrom);
            if($keyTo === '') {
                return $value;
            }
            $toAccessor->set($keyTo, $value);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getValue($source, $key)
    {
        if($key === '' || $key === null) {
            return $source;
        }

        if($source === null || (!is_array($source) && !is_object($source))) {
            return $this->getValueFromUnsupportedSource($source, $key);
        }

        if(is_string($key)) {
            return $this->getValueByKey($source, $key);
        }

        if(is_array($key)) {
            return $this->getValueByFilters($source, $key);
        }

        return $this->getValueByUnsupportedKey($source, $key);
    }

    /**
     * @inheritDoc
     */
    public function setPathDelimiter(string $value): void
    {
        $this->pathDelimiter = $value;
    }

    /**
     * @inheritDoc
     */
    public function setErrorsLevelMask(int $value): void
    {
        $this->errorsLevelMask = $value;
    }

    /**
     * @inheritDoc
     */
    public function addFilter(string $filterName, callable $callback): self
    {
        $this->filterMap[$filterName] = $callback;
        return $this;
    }

    /**
     * Returns value got by string key
     * @param array<string, mixed>|object $source source to get value from
     * @param string $key nested path to get value by
     * @return array|mixed|null value
     * @throws SchematorException
     */
    protected function getValueByKey($source, string $key)
    {
        try {
            $fromAccessor = $this->nestedAccessorFactory->create($source, $this->pathDelimiter);
            return $fromAccessor->get(
                $key,
                BitmapHelper::intersects($this->errorsLevelMask, [SchematorException::CANNOT_GET_VALUE])
            );
        } catch(NestedAccessorException $e) {
            throw SchematorException::createAsCannotGetValue($source, $key, $e);
        }
    }

    /**
     * Returns value got by filters key
     * @param array<string, mixed>|object $source source to get value from
     * @param array<int, string|array<int, mixed>> $filters filters config
     * @return mixed|null
     * @throws SchematorException
     */
    protected function getValueByFilters($source, array $filters)
    {
        $result = $source;
        foreach($filters as $filterConfig) {
            if(is_string($filterConfig)) {
                $result = $this->getValue($result, $filterConfig);
            } elseif(is_array($filterConfig)) {
                $result = $this->runFilter($filterConfig, $result, $source);
            } else {
                if(
                    BitmapHelper::intersects(
                        $this->errorsLevelMask,
                        [SchematorException::UNSUPPORTED_FILTER_CONFIG_TYPE]
                    )
                ) {
                    throw SchematorException::createAsUnsupportedFilterConfigType($filterConfig);
                }
                $result = null;
            }
        }

        return $result;
    }

    /**
     * Returns value got from unsupported source
     * @param mixed $source unsupported source
     * @param mixed $key path to get value by
     * @return null the only value we can get from unsupported source
     * @throws SchematorException
     */
    protected function getValueFromUnsupportedSource($source, $key)
    {
        if(BitmapHelper::intersects($this->errorsLevelMask, [SchematorException::UNSUPPORTED_SOURCE_TYPE])) {
            throw SchematorException::createAsUnsupportedSourceType($source, $key);
        }
        return null;
    }

    /**
     * Returns value got by unsupported key
     * @param array<string, mixed>|object $source source to get value from
     * @param mixed $key unsupported key
     * @return null the only value we can get by unsupported key
     * @throws SchematorException
     */
    protected function getValueByUnsupportedKey($source, $key)
    {
        if(BitmapHelper::intersects($this->errorsLevelMask, [SchematorException::UNSUPPORTED_KEY_TYPE])) {
            throw SchematorException::createAsUnsupportedKeyType($source, $key);
        }
        return null;
    }

    /**
     * Returns value from source by filter
     * @param array<int, mixed> $filterConfig filter config [filterName, ...args]
     * @param array<string, mixed>|object|mixed $source source to extract value from
     * @param array<string, mixed>|object $rootSource root source
     * @return mixed result value
     * @throws SchematorException
     */
    protected function runFilter(array $filterConfig, $source, $rootSource)
    {
        $filterName = strval(array_shift($filterConfig));

        if(
            !isset($this->filterMap[$filterName])
            && BitmapHelper::intersects($this->errorsLevelMask, [SchematorException::FILTER_NOT_FOUND])
        ) {
            throw SchematorException::createAsFilterNotFound($filterName);
        }

        try {
            return $this->filterMap[$filterName](
                new FilterContext($this, $source, $rootSource),
                ...$filterConfig
            );
        } catch(Throwable $e) {
            if(BitmapHelper::intersects($this->errorsLevelMask, [SchematorException::FILTER_ERROR])) {
                throw SchematorException::createAsFilterError($filterName, $filterConfig, $source, $e);
            }
            return null;
        }
    }
}
