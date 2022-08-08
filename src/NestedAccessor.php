<?php


namespace Smoren\Schemator;


use Smoren\Helpers\ArrHelper;
use Smoren\Schemator\Exceptions\NestedAccessorException;

class NestedAccessor
{
    /**
     * @var array
     */
    protected array $source; // TODO object?
    /**
     * @var string
     */
    protected string $pathDelimiter;

    /**
     * ArrayNestedAccessor constructor.
     * @param array|null $source
     * @param string $pathDelimiter
     */
    public function __construct(?array &$source, string $pathDelimiter)
    {
        if($source === null) {
            $source = [];
        }

        /** @var array $source */
        $this->source = &$source;
        $this->pathDelimiter = $pathDelimiter;
    }

    /**
     * @param string $path
     * @param null $defaultValue
     * @param bool $strict
     * @return array|mixed|null
     * @throws NestedAccessorException
     */
    public function get(string $path, $defaultValue = null, bool $strict = false)
    {
        try {
            return $this->_get(
                $this->source, array_reverse(explode($this->pathDelimiter, $path)), $strict
            ) ?? $defaultValue;
        } catch(NestedAccessorException $e) {
            if($strict && $defaultValue === null) {
                throw $e;
            }
            return $defaultValue;
        }
    }

    /**
     * @param string $path
     * @param $value
     * @return $this
     */
    public function set(string $path, $value): self
    {
        return $this->_set($this->source, explode($this->pathDelimiter, $path), $value);
    }

    /**
     * @return array
     */
    public function getSource(): array
    {
        return $this->source;
    }

    /**
     * @param array $source
     * @param array $arPath
     * @param bool $strict
     * @return array|mixed
     * @throws NestedAccessorException
     */
    protected function _get(array $source, array $arPath, bool $strict)
    {
        if(!count($arPath)) {
            return $source;
        }

        $key = array_pop($arPath);

        if(!array_key_exists($key, $source)) {
            if(!$strict) {
                return null;
            }
            // TODO need testing
            throw NestedAccessorException::createAsKeyNotFound($key, $source);
        }

        $source = $source[$key];

        if(count($arPath)) {
            if(ArrHelper::isAssoc($source)) {
                return $this->_get($source, $arPath, $strict);
            }

            $subValues = [];
            foreach($source as $sourceItem) {
                $subValues[] = $this->_get($sourceItem, $arPath, $strict);
            }
            return $subValues;
        }

        return $source;
    }

    /**
     * @param array $source
     * @param array $arPath
     * @param $value
     * @return $this
     */
    protected function _set(array &$source, array $arPath, $value): self
    {
        $temp = &$source;
        foreach($arPath as $key) {
            $temp = &$temp[$key];
        }
        $temp = $value;
        unset($temp);

        return $this;
    }
}