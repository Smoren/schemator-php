<?php

namespace Smoren\Schemator\Components;

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
    /**
     * @var array<string, callable> filters map
     */
    protected array $filters = [];
    /**
     * @var non-empty-string delimiter for multilevel paths
     */
    protected string $pathDelimiter;
    /**
     * @var NestedAccessorFactoryInterface nested accessor factory
     */
    protected NestedAccessorFactoryInterface $nestedAccessorFactory;

    /**
     * Schemator constructor.
     * @param non-empty-string $pathDelimiter delimiter for multilevel paths
     */
    public function __construct(
        string $pathDelimiter = '.',
        NestedAccessorFactoryInterface $nestedAccessorFactory = null
    ) {
        $this->pathDelimiter = $pathDelimiter;
        $this->nestedAccessorFactory = $nestedAccessorFactory ?? new NestedAccessorFactory();
    }

    /**
     * @inheritDoc
     */
    public function convert($source, array $schema, bool $strict = false)
    {
        $toAccessor = $this->nestedAccessorFactory->create($result, $this->pathDelimiter);

        foreach($schema as $keyTo => $keyFrom) {
            $value = $this->getValue($source, $keyFrom, $strict);
            if($keyTo === '') {
                return $value;
            }
            $toAccessor->set($keyTo, $value, $strict);
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @deprecated please use convert() method
     */
    public function exec($source, array $schema, bool $strict = false)
    {
        return $this->convert($source, $schema, $strict);
    }

    /**
     * @inheritDoc
     */
    public function getValue($source, $key, bool $strict = false)
    {
        if($key === '' || $key === null) {
            return $source;
        }

        if($source === null || (!is_array($source) && !is_object($source))) {
            return $this->getValueFromUnsupportedSource($source, $key, $strict);
        }

        if(is_string($key)) {
            return $this->getValueByKey($source, $key, $strict);
        }

        if(is_array($key)) {
            return $this->getValueByFilters($source, $key, $strict);
        }

        return $this->getValueByUnsupportedKey($source, $key, $strict);
    }

    /**
     * @inheritDoc
     */
    public function addFilter(string $filterName, callable $callback): self
    {
        $this->filters[$filterName] = $callback;
        return $this;
    }

    /**
     * Returns value got by string key
     * @param array<string, mixed>|object $source source to get value from
     * @param string $key nested path to get value by
     * @param bool $strict when true throw exception if something goes wrong
     * @return array|mixed|null value
     * @throws SchematorException
     */
    protected function getValueByKey($source, string $key, bool $strict)
    {
        try {
            $fromAccessor = $this->nestedAccessorFactory->create($source, $this->pathDelimiter);
            return $fromAccessor->get($key, $strict);
        } catch(NestedAccessorException $e) {
            throw SchematorException::createAsCannotGetValue($source, $key, $e);
        }
    }

    /**
     * Returns value got by filters key
     * @param array<string, mixed>|object $source source to get value from
     * @param array<int, mixed> $filters filters config
     * @param bool $strict when true throw exception if something goes wrong
     * @return array|mixed|null
     * @throws SchematorException
     */
    protected function getValueByFilters($source, array $filters, bool $strict)
    {
        $result = $source;
        foreach($filters as $filterConfig) {
            if(is_string($filterConfig)) {
                $result = $this->getValue($result, $filterConfig, $strict);
            } elseif(is_array($filterConfig)) {
                $result = $this->runFilter($filterConfig, $result, $source, $strict);
            } else {
                if($strict) {
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
     * @param bool $strict when true throw exception
     * @return null the only value we can get from unsupported source
     * @throws SchematorException
     */
    protected function getValueFromUnsupportedSource($source, $key, bool $strict)
    {
        if(!$strict) {
            return null;
        }
        throw SchematorException::createAsUnsupportedSourceType($source, $key);
    }

    /**
     * Returns value got by unsupported key
     * @param array<string, mixed>|object $source source to get value from
     * @param mixed $key unsupported key
     * @param bool $strict when true throw exception
     * @return null the only value we can get by unsupported key
     * @throws SchematorException
     */
    protected function getValueByUnsupportedKey($source, $key, bool $strict)
    {
        if(!$strict) {
            return null;
        }
        throw SchematorException::createAsUnsupportedKeyType($source, $key);
    }

    /**
     * Returns value from source by filter
     * @param array<int, mixed> $filterConfig filter config [filterName, ...args]
     * @param array<string, mixed>|object $source source to extract value from
     * @param array<string, mixed>|object $rootSource root source
     * @param bool $strict when true throw exception if something goes wrong
     * @return mixed result value
     * @throws SchematorException
     */
    protected function runFilter(array $filterConfig, $source, $rootSource, bool $strict)
    {
        $filterName = array_shift($filterConfig);

        SchematorException::ensureFilterExists($this->filters, $filterName);

        try {
            return $this->filters[$filterName](
                new FilterContext($this, $source, $rootSource),
                ...$filterConfig
            );
        } catch(Throwable $e) {
            if($strict) {
                throw SchematorException::createAsFilterError($filterName, $filterConfig, $source, $e);
            }
            return null;
        }
    }
}
