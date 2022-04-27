<?php


namespace Smoren\Helpers\components;


use Smoren\ExtendedExceptions\BadDataException;

class Schemator
{
    /**
     * @var callable[]
     */
    protected $filters = [];
    /**
     * @var string
     */
    protected $pathDelimiter;

    /**
     * Schemator constructor.
     * @param string $pathDelimiter
     */
    public function __construct(string $pathDelimiter = '.')
    {
        $this->pathDelimiter = $pathDelimiter;
    }

    /**
     * @param array $schema
     * @param array $source
     * @return array
     * @throws BadDataException
     */
    public function exec(array $schema, array $source): array
    {
        $result = [];

        foreach($schema as $key => $schemaItem) {
            $result[$key] = $this->getValue($source, $schemaItem);
        }

        return $result;
    }

    /**
     * @param string $filterName
     * @param callable $callback
     * @return $this
     */
    public function addFilter(string $filterName, callable $callback): self
    {
        $this->filters[$filterName] = $callback;
        return $this;
    }

    /**
     * @param array $source
     * @param $schemaItem
     * @return array|mixed|null
     * @throws BadDataException
     */
    public function getValue(array $source, $schemaItem)
    {
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
     * @param array $source
     * @param array $path
     * @return mixed
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
            if($this->isArrayAssoc($source)) {
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
     * @param array $filterConfig
     * @param $source
     * @return mixed
     * @throws BadDataException
     */
    protected function runFilter(array $filterConfig, $source, $rootSource)
    {
        $filterName = array_shift($filterConfig);

        if(!isset($this->filters[$filterName])) {
            throw new BadDataException("filter '{$filterName}' not found", 1);
        }

        return $this->filters[$filterName]($this, $source, $rootSource, ...$filterConfig);
    }

    /**
     * @param array $input
     * @return bool
     */
    protected function isArrayAssoc(array $input): bool
    {
        if([] === $input) return false;
        return array_keys($input) !== range(0, count($input) - 1);
    }
}