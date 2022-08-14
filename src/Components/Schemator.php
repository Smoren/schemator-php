<?php


namespace Smoren\Schemator\Components;


use Smoren\Schemator\Exceptions\NestedAccessorException;
use Smoren\Schemator\Exceptions\SchematorException;
use Smoren\Schemator\Factories\NestedAccessorFactory;
use Smoren\Schemator\Interfaces\NestedAccessorFactoryInterface;
use Smoren\Schemator\Interfaces\SchematorInterface;
use Throwable;

/**
 * Class for schematic data converting
 * @author Smoren <ofigate@gmail.com>
 */
class Schemator implements SchematorInterface
{
    /**
     * @var callable[] filters map
     */
    protected array $filters = [];
    /**
     * @var string delimiter for multilevel paths
     */
    protected string $pathDelimiter;
    /**
     * @var NestedAccessorFactoryInterface nested accessor factory
     */
    protected NestedAccessorFactoryInterface $nestedAccessorFactory;

    /**
     * Schemator constructor.
     * @param string $pathDelimiter delimiter for multilevel paths
     */
    public function __construct(
        string $pathDelimiter = '.',
        NestedAccessorFactoryInterface $nestedAccessorFactory = null
    )
    {
        $this->pathDelimiter = $pathDelimiter;
        $this->nestedAccessorFactory = $nestedAccessorFactory ?? new NestedAccessorFactory();
    }

    /**
     * Converts input data with using schema
     * @param array|object $source input data to convert
     * @param array $schema schema for converting
     * @param bool $strict throw exception if key not exist
     * @return array|mixed converted data
     * @throws SchematorException
     */
    public function exec($source, array $schema, bool $strict = false)
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
     * Returns value from source by schema item
     * @param array|object|null $source source to extract data from
     * @param string|array $key item of schema (string as path or array as filter config)
     * @param bool $strict throw exception if key not exist
     * @return mixed result value
     * @throws SchematorException
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
     * Adds new filter
     * @param string $filterName filter name
     * @param callable $callback filter callback
     * @return $this
     */
    public function addFilter(string $filterName, callable $callback): self
    {
        $this->filters[$filterName] = $callback;
        return $this;
    }

    /**
     * @param array $source
     * @param string $key
     * @param bool $strict
     * @return array|mixed|null
     * @throws SchematorException
     */
    protected function getValueByKey(array $source, string $key, bool $strict)
    {
        try {
            $fromAccessor = $this->nestedAccessorFactory->create($source, $this->pathDelimiter);
            return $fromAccessor->get($key, $strict);
        } catch(NestedAccessorException $e) {
            throw SchematorException::createAsCannotGetValue($source, $key, $e);
        }
    }

    /**
     * @param array $source
     * @param array $filters
     * @param bool $strict
     * @return array|mixed|null
     * @throws SchematorException
     */
    protected function getValueByFilters(array $source, array $filters, bool $strict)
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
     * @param mixed $source
     * @param mixed $key
     * @param bool $strict
     * @return null
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
     * @param mixed $source
     * @param mixed $key
     * @param bool $strict
     * @return null
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
     * @param array $filterConfig filter config [filterName, ...args]
     * @param mixed $source source to extract value from
     * @param array $rootSource root source
     * @param bool $strict
     * @return mixed result value
     * @throws SchematorException
     */
    protected function runFilter(array $filterConfig, $source, array $rootSource, bool $strict)
    {
        $filterName = array_shift($filterConfig);

        SchematorException::ensureFilterExists($this->filters, $filterName);

        try {
            return $this->filters[$filterName]($this, $source, $rootSource, ...$filterConfig);
        } catch(Throwable $e) {
            if($strict) {
                throw SchematorException::createAsFilterError($filterName, $filterConfig, $source, $e);
            }

            return null;
        }
    }
}
