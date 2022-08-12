<?php


namespace Smoren\Schemator\Components;


use Smoren\Helpers\ArrHelper;
use Smoren\Schemator\Exceptions\NestedAccessorException;
use Smoren\Schemator\Interfaces\NestedAccessorInterface;

class NestedAccessor implements NestedAccessorInterface
{
    /**
     * @var array|object
     */
    protected $source;
    /**
     * @var string
     */
    protected string $pathDelimiter;

    /**
     * ArrayNestedAccessor constructor.
     * @param array|object|null $source
     * @param string $pathDelimiter
     * @throws NestedAccessorException
     */
    public function __construct(&$source, string $pathDelimiter = '.')
    {
        if($source === null) {
            $source = [];
        }

        if(is_scalar($source)) {
            throw NestedAccessorException::createAsSourceIsScalar($source);
        }

        /** @var array $source */
        $this->source = &$source;
        $this->pathDelimiter = $pathDelimiter;
    }

    /**
     * @param string|null $path
     * @param bool $strict
     * @return array|object|null
     * @throws NestedAccessorException
     */
    public function get(?string $path = null, bool $strict = true)
    {
        if($path === null || $path === '') {
            return $this->source;
        }

        $result = null;
        $errorsCount = 0;

        $this->_get(
            $this->source,
            array_reverse(explode($this->pathDelimiter, $path)),
            $result,
            $errorsCount
        );

        if($strict && $errorsCount) {
            throw NestedAccessorException::createAsKeyNotFound($path, $errorsCount);
        }

        return $result;
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
     * @param mixed $source
     * @param array $path
     * @param array|mixed $result
     * @param int $errorsCount
     */
    protected function _get($source, array $path, &$result, int &$errorsCount)
    {
        if(!count($path)) {
            if(is_array($result)) {
                $result[] = $source;
            } else {
                $result = $source;
            }
            return;
        }

        while(count($path)) {
            $key = array_pop($path);
            $pathPassed[] = $key;

            if(is_array($source)) {
                if(!array_key_exists($key, $source)) {
                    $errorsCount++;
                    return;
                }
                $source = $source[$key];
            } elseif(is_object($source)) {
                if(!property_exists($source, $key)) {
                    $errorsCount++;
                    return;
                }
                $source = $source->{$key};
            } else {
                $errorsCount++;
                return;
            }

            if(count($path) && is_array($source) && !ArrHelper::isAssoc($source)) {
                if(!is_array($result)) {
                    $result = [];
                }
                foreach($source as $item) {
                    $this->_get($item, $path, $result, $errorsCount);
                }
                return;
            }
        }

        $this->_get($source, $path, $result, $errorsCount);
    }

    /**
     * @param array|object $source
     * @param array $path
     * @param $value
     * @return $this
     */
    protected function _set(&$source, array $path, $value): self
    {
        $temp = &$source;
        foreach($path as $key) {
            if(isset($temp) && is_scalar($temp)) {
                // value in the middle of the path must me an array
                $temp = [];
            }

            if(is_object($source)) {
                $temp = &$temp->{$key};
            } else {
                $temp = &$temp[$key];
            }
        }
        $temp = $value;
        unset($temp);

        return $this;
    }
}