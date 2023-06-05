<?php

namespace Smoren\Schemator\Components;

use Smoren\Schemator\Helpers\ContainerAccessHelper;

class NestedAccessor
{
    /**
     * @var array|object
     */
    protected $source;
    protected string $pathDelimiter;

    /**
     * @param array|object $source
     * @param string $pathDelimiter
     */
    public function __construct(&$source, string $pathDelimiter = '.')
    {
        $this->source = &$source;
        $this->pathDelimiter = $pathDelimiter;
    }

    /**
     * @param string|string[]|null $path
     * @return mixed
     */
    public function get($path = null, bool $strict = true)
    {
        return $this->getInternal($this->source, $this->getPathArray($path), [], $strict);
    }

    /**
     * @param mixed $carry
     * @param string[] $pathToTravel
     * @param bool $strict
     * @return mixed
     */
    protected function getInternal($carry, array $pathToTravel, array $traveledPath, bool $strict)
    {
        while (count($pathToTravel)) {
            $key = array_pop($pathToTravel);

            if ($key === '*') {
                if (!is_iterable($carry)) {
                    return $this->handleError($key, $traveledPath, $strict);
                }

                $result = [];
                $traveledPath[] = $key;
                foreach ($carry as $item) {
                    $result[] = $this->getInternal($item, $pathToTravel, $traveledPath, $strict);
                }

                return $result;
            }

            if ($key === '>') {
                if (!is_iterable($carry)) {
                    return $this->handleError($key, $traveledPath, $strict);
                }

                $result = [];
                foreach ($carry as $item) {
                    if (!is_iterable($item)) {
                        if ($strict) {
                            return $this->handleError($key, $traveledPath, true);
                        }
                        continue;
                    }
                    foreach ($item as $subItem) {
                        $result[] = $subItem;
                    }
                }
                $traveledPath[] = $key;

                return $this->getInternal($result, $pathToTravel, $traveledPath, $strict);
            }

            if (!ContainerAccessHelper::exists($carry, $key)) {
                return $this->handleError($key, $traveledPath, $strict);
            }

            $carry = ContainerAccessHelper::get($carry, $key);
            $traveledPath[] = $key;
        }

        return $carry;
    }

    /**
     * @param string|string[]|null $path
     * @return string[]
     */
    protected function getPathArray($path): array
    {
        if ($path === null) {
            return [];
        }

        if (is_string($path)) {
            $path = explode($this->pathDelimiter, $path);
        }

        if (is_numeric($path)) {
            $path = [strval($path)];
        }

        if (!is_array($path)) {
            $type = gettype($path);
            throw new \InvalidArgumentException("Path must be numeric, string or array, {$type} given");
        }

        return array_reverse($path);
    }

    /**
     * @param string[] $path
     * @return string
     */
    protected function getPathString(array $path): string
    {
        return implode($this->pathDelimiter, $path);
    }

    /**
     * @param string $key
     * @param string[] $path
     * @param bool $strict
     * @return null
     */
    protected function handleError(string $key, array $path, bool $strict)
    {
        if (!$strict) {
            return null;
        }

        throw new \UnexpectedValueException(
            "Key '{$key}' is not found on path '{$this->getPathString($path)}'"
        );
    }
}
