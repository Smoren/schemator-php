<?php

namespace Smoren\Schemator\Components;

use Smoren\Helpers\ArrHelper;
use Smoren\Schemator\Exceptions\NestedAccessorException;
use Smoren\Schemator\Interfaces\NestedAccessorInterface;

/**
 * Accessor class for getting and setting to source array or object with nested keys
 * @author Smoren <ofigate@gmail.com>
 */
class NestedAccessor implements NestedAccessorInterface
{
    /**
     * @var array|object data source for accessing
     */
    protected $source;
    /**
     * @var string path's separator of nesting
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
     * Getter of source part specified by nested path
     * @param string|null $path nested path
     * @param bool $strict if true: throw exception when path is not found in source
     * @return mixed value from source got by nested path
     * @throws NestedAccessorException if strict mode on and path is not found in source
     */
    public function get(?string $path = null, bool $strict = true)
    {
        // when path is not specified
        if($path === null || $path === '') {
            // let's return the full source
            return $this->source;
        }

        // let result be null and there are no errors by default
        $result = null;
        $errorsCount = 0;

        // getting result with internal recursive method
        $this->_get(
            $this->source,
            array_reverse(explode($this->pathDelimiter, $path)), // path stack
            $result,
            $errorsCount
        );

        // when strict mode is on and we got errors
        if($strict && $errorsCount) {
            throw NestedAccessorException::createAsCannotGetValue($path, $errorsCount);
        }

        return $result;
    }

    /**
     * Setter of source part specified by nested path
     * @param string $path nested path
     * @param mixed $value value to save by path
     * @param bool $strict when true throw exception if path not exist in source object
     * @return $this
     * @throws NestedAccessorException
     */
    public function set(string $path, $value, bool $strict = true): self
    {
        return $this->_set($this->source, explode($this->pathDelimiter, $path), $value, $strict);
    }

    /**
     * Internal recursive method to get value from source by path stack
     * @param mixed $source source to get value from
     * @param array $path nested path stack
     * @param array|mixed $result place for result
     * @param int $errorsCount errors counter
     */
    protected function _get($source, array $path, &$result, int &$errorsCount)
    {
        // if path stack is empty — we reached target value of given path in source argument
        if(!count($path)) {
            // so if result is multiple
            if(is_array($result)) {
                // we append source to result
                $result[] = $source;
            } else {
                // result is single
                $result = $source;
            }
            // we don't need to do something in this recursive branch
            return;
        }

        // let's iterate every path part from stack
        while(count($path)) {
            $key = array_pop($path);

            if(is_array($source)) {
                if(!array_key_exists($key, $source)) {
                    // path part key is missing in source array
                    $errorsCount++;
                    // we cannot go deeper
                    return;
                }
                // go to the next nested level
                $source = $source[$key];
            } elseif(is_object($source)) {
                if(!property_exists($source, $key)) {
                    // path part key is missing in source object
                    $errorsCount++;
                    // we cannot go deeper
                    return;
                }
                // go to the next nested level
                $source = $source->{$key};
            } else {
                // source is scalar, so we can't go to the next depth level
                $errorsCount++;
                // we cannot go deeper
                return;
            }

            // when it's not the last iteration of the stack
            // and the source is non-associative array (list)
            if(count($path) && is_array($source) && !ArrHelper::isAssoc($source)) {
                // the result will be multiple
                if(!is_array($result)) {
                    $result = [];
                }
                // and we need to use recursive call for each item of this array
                foreach($source as $item) {
                    $this->_get($item, $path, $result, $errorsCount);
                }
                // we don't need to do something in this recursive branch
                return;
            }
        }

        // if all the path successfully passed
        // we need only one recursive call to save source to result
        $this->_get($source, $path, $result, $errorsCount);
    }

    /**
     * Internal recursive method to save value to source by path stack
     * @param array|object $source source to save value to
     * @param array $path nested path
     * @param mixed $value value to save to source
     * @param bool $strict when true throw exception if path not exist in source object
     * @return $this
     * @throws NestedAccessorException
     */
    protected function _set(&$source, array $path, $value, bool $strict): self
    {
        $temp = &$source;
        // let's iterate every path part to go deeper into nesting
        foreach($path as $key) {
            if(isset($temp) && is_scalar($temp)) {
                // value in the middle of the path must me an array
                $temp = [];
            }

            // go to the next nested level
            if(is_object($temp)) {
                if($strict && !property_exists($temp, $key)) {
                    throw NestedAccessorException::createAsCannotSetValue($key);
                }
                $temp = &$temp->{$key};
            } else {
                $temp = &$temp[$key];
            }
        }
        // now we can save value to the source
        $temp = $value;
        unset($temp);

        return $this;
    }
}
