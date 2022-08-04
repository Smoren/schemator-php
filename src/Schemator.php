<?php


namespace Smoren\Schemator;


use Smoren\Helpers\ArrHelper;
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
     * @return array converted data
     * @throws SchematorException
     */
    public function exec(array $source, array $schema): array
    {
        $result = [];

        foreach($schema as $key => $schemaItem) {
            $this->saveByPath($result, $key, $this->getValue($source, $schemaItem));
        }

        return $result;
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
     * Returns value from source by schema item
     * @param array|null $source source to extract data from
     * @param string|array $schemaItem item of schema (string as path or array as filter config)
     * @return mixed result value
     * @throws SchematorException
     */
    public function getValue(?array $source, $schemaItem)
    {
        if($source === null) {
            return null;
        }

        if(is_string($schemaItem)) {
            if($schemaItem === '') {
                return $source;
            }
            return $this->getValueRecursive($source, explode($this->pathDelimiter, $schemaItem));
        }

        if(is_array($schemaItem)) {
            $result = $source;
            foreach($schemaItem as $filterConfig) {
                if(is_string($filterConfig)) {
                    $result = $this->getValue($result, $filterConfig);
                } elseif(is_array($filterConfig)) {
                    $result = $this->runFilter($filterConfig, $result, $source);
                } else {
                    $result = null;
                }
            }

            return $result;
        }

        return null;
    }

    /**
     * Internal recursive method to extract value from source
     * @param array $source source to extract value from
     * @param array $path path to extract value by
     * @return mixed result value
     */
    protected function getValueRecursive(array $source, array $path)
    {
        if(!count($path)) {
            return null;
        }

        $pathItem = array_shift($path);

        if(!isset($source[$pathItem])) {
            return null;
        }

        $source = $source[$pathItem];

        if(count($path)) {
            if(ArrHelper::isAssoc($source)) {
                return $this->getValueRecursive($source, $path);
            }

            $subValues = [];

            foreach($source as $sourceItem) {
                $subValues[] = $this->getValueRecursive($sourceItem, $path);
            }

            return $subValues;
        }

        if(is_object($source)) {
            return null;
        }

        return $source;
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

        if(!isset($this->filters[$filterName])) {
            throw new SchematorException(
                "filter '{$filterName}' not found",
                SchematorException::FILTER_NOT_FOUND
            );
        }

        try {
            return $this->filters[$filterName]($this, $source, $rootSource, ...$filterConfig);
        } catch(Throwable $e) {
            throw new SchematorException(
                "filter error: '{$filterName}'",
                SchematorException::FILTER_ERROR,
                $e,
                [
                    'error' => $e->getMessage(),
                    'name' => $filterName,
                    'config' => $filterConfig,
                    'source' => $source,
                ]
            );
        }
    }

    /**
     * Creates path and saves the value by it
     * @param array $source source container to save value to
     * @param string $path path destination of value
     * @param mixed $value value to save
     * @return $this
     */
    protected function saveByPath(array &$source, string $path, $value): self
    {
        $arPath = explode($this->pathDelimiter, $path);
        $temp = &$source;
        foreach($arPath as $key) {
            $temp = &$temp[$key];
        }
        $temp = $value;
        unset($temp);

        return $this;
    }
}
