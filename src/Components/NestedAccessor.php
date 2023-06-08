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
     * @throws \UnexpectedValueException
     */
    public function get($path = null, bool $strict = true)
    {
        return $this->getInternal($this->source, $this->getPathArray($path), $strict);
    }

    /**
     * @param mixed $carry
     * @param string[] $pathToTravel
     * @param bool $strict
     * @return mixed
     * @throws \UnexpectedValueException
     */
    protected function getInternal($carry, array $pathToTravel, bool $strict)
    {
        $traveledPath = [];
        $isResultMultiple = false;
        while (count($pathToTravel)) {
            $key = array_pop($pathToTravel);
            $prevKey = count($traveledPath)
                ? $traveledPath[count($traveledPath) - 1]
                : null;

            if ($key === '|') {
                $isResultMultiple = false;
                $traveledPath[] = $key;
                continue;
            }

            if (preg_match('/^[*]+$/', $key)) {
                for ($i = 0; $i < strlen($key) - 1; ++$i) {
                    $pathToTravel[] = '*';
                }
                $key = '*';
            }

            if ($key === '*') {
                if (!is_iterable($carry)) {
                    return $this->handleError($key, $traveledPath, $isResultMultiple, $strict);
                }

                $result = [];

                if ($prevKey === '*') {
                    foreach ($carry as $item) {
                        if (!is_iterable($item)) {
                            if ($strict) {
                                return $this->handleError($key, $traveledPath, $isResultMultiple, $strict);
                            }
                            continue;
                        }
                        foreach ($item as $subItem) {
                            $result[] = $subItem;
                        }
                    }
                } else {
                    foreach ($carry as $item) {
                        $result[] = $item;
                    }
                }

                $isResultMultiple = true;
                $traveledPath[] = $key;
                $carry = $result;

                continue;
            }

            if ($prevKey === '*') {
                $result = [];
                foreach ($carry as $item) {
                    if (!ContainerAccessHelper::exists($item, $key)) {
                        if ($strict) {
                            return $this->handleError($key, $traveledPath, $isResultMultiple, $strict);
                        }
                        continue;
                    }
                    $result[] = ContainerAccessHelper::get($item, $key);
                }
                $traveledPath[] = $key;
                $carry = $result;

                continue;
            }

            if (!ContainerAccessHelper::exists($carry, $key)) {
                return $this->handleError($key, $traveledPath, $isResultMultiple, $strict);
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
     * @param bool $isResultMultiple
     * @param bool $strict
     * @return null|array{}
     */
    protected function handleError(string $key, array $path, bool $isResultMultiple, bool $strict): ?array
    {
        if (!$strict) {
            return $isResultMultiple ? [] : null;
        }

        throw new \UnexpectedValueException(
            "Key '{$key}' is not found on path '{$this->getPathString($path)}'"
        );
    }
}
