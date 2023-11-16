<?php

declare(strict_types=1);

namespace Smoren\Schemator\Components;

use Smoren\Schemator\Exceptions\PathException;
use Smoren\Schemator\Exceptions\SchematorException;
use Smoren\Schemator\Factories\NestedAccessorFactory;
use Smoren\Schemator\Interfaces\BitmapInterface;
use Smoren\Schemator\Interfaces\NestedAccessorFactoryInterface;
use Smoren\Schemator\Interfaces\SchematorInterface;
use Smoren\Schemator\Structs\Bitmap;
use Smoren\Schemator\Structs\ErrorsLevelMask;
use Smoren\Schemator\Structs\FilterContext;
use Throwable;
use TypeError;

/**
 * Class for schematic data converting
 * @author Smoren <ofigate@gmail.com>
 */
class Schemator implements SchematorInterface
{
    /**
     * @var array<string, callable> filters map
     */
    protected array $filterMap = [];
    /**
     * @var non-empty-string delimiter for multilevel paths
     */
    protected string $pathDelimiter;
    /**
     * @var BitmapInterface bitmap errors level mask
     */
    protected BitmapInterface $errorsLevelMask;
    /**
     * @var NestedAccessorFactoryInterface nested accessor factory
     */
    protected NestedAccessorFactoryInterface $nestedAccessorFactory;

    /**
     * Schemator constructor.
     * @param non-empty-string $pathDelimiter delimiter for multilevel paths
     * @param BitmapInterface|null $errorsLevelMask bitmap errors level mask
     * @param NestedAccessorFactoryInterface|null $nestedAccessorFactory nested accessor factory
     */
    public function __construct(
        string $pathDelimiter = '.',
        ?BitmapInterface $errorsLevelMask = null,
        NestedAccessorFactoryInterface $nestedAccessorFactory = null
    ) {
        $this->pathDelimiter = $pathDelimiter;
        $this->errorsLevelMask = $errorsLevelMask ?? ErrorsLevelMask::default();
        $this->nestedAccessorFactory = $nestedAccessorFactory ?? new NestedAccessorFactory();
    }

    /**
     * @inheritDoc
     * @throws SchematorException
     */
    public function convert($source, array $schema)
    {
        $result = [];
        $toAccessor = $this->nestedAccessorFactory->create($result, $this->pathDelimiter);

        foreach ($schema as $keyTo => $keyFrom) {
            $value = $this->getValue($source, $keyFrom);
            if ($keyTo === '') {
                return $value;
            }
            $toAccessor->set($keyTo, $value);
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @throws SchematorException
     */
    public function getValue($source, $key)
    {
        if ($key === '' || $key === null) {
            return $source;
        }

        if (!is_array($source) && !is_object($source)) {
            return $this->getValueFromUnsupportedSource($source, $key);
        }

        if (is_string($key)) {
            return $this->getValueByKey($source, $key);
        }

        if (is_array($key)) {
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
    public function setErrorsLevelMask(BitmapInterface $value): void
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
                $this->needToThrow(SchematorException::CANNOT_GET_VALUE)
            );
        } catch (PathException $e) {
            throw SchematorException::createAsCannotGetValue($source, $key, $e);
        }
    }

    /**
     * Returns value got by filters key
     * @param array<string, mixed>|object $source source to get value from
     * @param array<int, string|array<int, mixed>|null> $filters filters config
     * @return mixed|null
     * @throws SchematorException
     */
    protected function getValueByFilters($source, array $filters)
    {
        $result = $source;
        foreach ($filters as $filterConfig) {
            if (is_string($filterConfig) || $filterConfig === null) {
                $result = $this->getValue($result, $filterConfig);
            } elseif (is_array($filterConfig)) {
                $result = $this->runFilter($filterConfig, $result, $source);
            } else {
                if ($this->needToThrow(SchematorException::UNSUPPORTED_FILTER_CONFIG_TYPE)) {
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
        if ($this->needToThrow(SchematorException::UNSUPPORTED_SOURCE_TYPE)) {
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
        if ($this->needToThrow(SchematorException::UNSUPPORTED_KEY_TYPE)) {
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
        /** @var scalar $filterName */
        $filterName = array_shift($filterConfig);
        $filterName = strval($filterName);

        if (
            !isset($this->filterMap[$filterName])
            && $this->needToThrow(SchematorException::FILTER_NOT_FOUND)
        ) {
            throw SchematorException::createAsFilterNotFound($filterName);
        }

        $filterContext = new FilterContext($this, $source, $rootSource, $filterConfig, $filterName);
        try {
            return $this->filterMap[$filterName](
                $filterContext,
                ...$filterConfig
            );
        } catch (TypeError $e) {
            if ($this->needToThrow(SchematorException::BAD_FILTER_CONFIG)) {
                throw SchematorException::createAsBadFilterConfig($filterContext, $e);
            }
            return null;
        } catch (SchematorException $e) {
            if ($this->needToThrow($e->getCode())) {
                throw $e;
            }
            return null;
        } catch (Throwable $e) {
            if ($this->needToThrow(SchematorException::FILTER_ERROR)) {
                throw SchematorException::createAsFilterError($filterContext);
            }
            return null;
        }
    }

    /**
     * Returns true if there is given error code in errors level mask
     * @param int $errorCode
     * @return bool
     */
    protected function needToThrow(int $errorCode): bool
    {
        return $this->errorsLevelMask->intersectsWith(Bitmap::create([$errorCode]));
    }
}
