<?php


namespace Smoren\Schemator;


use Smoren\Schemator\Exceptions\NestedAccessorException;
use Smoren\Schemator\Exceptions\SchematorException;
use Throwable;

/**
 * Class for schematic data converting
 * @author Smoren <ofigate@gmail.com>
 */
class Schemator
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
     * Schemator constructor.
     * @param string $pathDelimiter delimiter for multilevel paths
     */
    public function __construct(string $pathDelimiter = '.')
    {
        $this->pathDelimiter = $pathDelimiter;
    }

    /**
     * Converts input data with using schema
     * @param array $source input data to convert
     * @param array $schema schema for converting
     * @param bool $strict throw exception if key not exist
     * @return array|mixed converted data
     * @throws SchematorException
     */
    public function exec(array $source, array $schema, bool $strict = false)
    {
        $toAccessor = new NestedAccessor($result, $this->pathDelimiter);

        foreach($schema as $keyTo => $keyFrom) {
            $value = $this->getValue($source, $keyFrom, $strict);
            if($keyTo === '') {
                return $value;
            }
            $toAccessor->set($keyTo, $value);
        }

        return $result;
    }

    /**
     * Returns value from source by schema item
     * @param array|null $source source to extract data from
     * @param string|array $key item of schema (string as path or array as filter config)
     * @param bool $strict throw exception if key not exist
     * @return mixed result value
     * @throws SchematorException
     */
    public function getValue(?array $source, $key, bool $strict = false)
    {
        if($key === '') {
            return $source;
        }

        if($source === null) {
            return $this->getValueOfNullSource($key, $strict);
        }

        if(is_string($key)) {
            return $this->getValueByKey($source, $key, $strict);
        }

        if(is_array($key)) {
            return $this->getValueByFilters($source, $key, $strict);
        }

        // TODO need testing, maybe exception?
        return null;
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
        $fromAccessor = new NestedAccessor($source, $this->pathDelimiter);
        try {
            return $fromAccessor->get($key, null, $strict);
        } catch(NestedAccessorException $e) {
            // TODO need testing
            throw SchematorException::createAsUnknownKey($key, $fromAccessor->getSource(), $e);
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
                $result = $this->runFilter($filterConfig, $result, $source);
            } else {
                // TODO need testing, maybe exception?
                $result = null;
            }
        }

        return $result;
    }

    /**
     * @param $key
     * @param bool $strict
     * @return null
     * @throws SchematorException
     */
    protected function getValueOfNullSource($key, bool $strict)
    {
        if(!$strict) {
            return null;
        }
        // TODO need testing
        throw SchematorException::createAsNullSource($key);
    }

    /**
     * Returns value from source by filter
     * @param array $filterConfig filter config [filterName, ...args]
     * @param mixed $source source to extract value from
     * @param array $rootSource root source
     * @return mixed result value
     * @throws SchematorException
     */
    protected function runFilter(array $filterConfig, $source, array $rootSource)
    {
        $filterName = array_shift($filterConfig);

        SchematorException::ensureFilterExists($this->filters, $filterName);

        try {
            return $this->filters[$filterName]($this, $source, $rootSource, ...$filterConfig);
        } catch(Throwable $e) {
            throw SchematorException::createAsFilterError($filterName, $filterConfig, $source, $e);
        }
    }
}
