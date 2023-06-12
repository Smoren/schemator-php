<?php

namespace Smoren\Schemator\Components;

use Smoren\Schemator\Exceptions\PathNotArrayException;
use Smoren\Schemator\Exceptions\PathNotExistException;
use Smoren\Schemator\Helpers\ContainerAccessHelper;

class NestedAccessor
{
    /**
     * @var array<mixed>|object
     */
    protected $source;
    /**
     * @var non-empty-string
     */
    protected string $pathDelimiter;

    /**
     * @param array<mixed>|object $source
     * @param non-empty-string $pathDelimiter
     */
    public function __construct(&$source, string $pathDelimiter = '.')
    {
        $this->source = &$source;
        $this->pathDelimiter = $pathDelimiter;
    }

    /**
     * @param string|string[]|null $path
     * @return mixed
     * @throws PathNotExistException
     */
    public function get($path = null, bool $strict = true)
    {
        return $this->getInternal($this->source, $this->getPathStack($path), $strict);
    }

    /**
     * @param string|string[]|null $path
     * @return bool
     */
    public function exist($path): bool
    {
        try {
            $this->get($path);
            return true;
        } catch (PathNotExistException $e) {
            return false;
        }
    }

    /**
     * @param string|string[]|null $path
     * @return bool
     */
    public function isset($path): bool
    {
        try {
            return $this->get($path) !== null;
        } catch (PathNotExistException $e) {
            return false;
        }
    }

    /**
     * @param string|string[]|null $path
     * @param mixed $value
     * @return $this
     */
    public function set($path, $value): self
    {
        $source = &$this->getRef($this->getPathStack($path));
        $source = $value;
        return $this;
    }

    /**
     * @param string|string[]|null $path
     * @param mixed $value
     * @return $this
     */
    public function append($path, $value): self
    {
        $this->checkIsArray($path);
        /** @var array<mixed> $source */
        $source = &$this->getRef($this->getPathStack($path));
        $source[] = $value;
        return $this;
    }

    /**
     * @param string|string[]|null $path
     * @return void
     */
    protected function checkIsArray($path): void
    {
        if (!$this->exist($path) || !is_array($this->get($path))) {
            $path = $this->getPathList($path);
            throw new PathNotArrayException(strval(array_pop($path)), $path, $this->pathDelimiter);
        }
    }

    /**
     * @param mixed $carry
     * @param string[] $pathStack
     * @param bool $strict
     * @return mixed
     * @throws PathNotExistException
     */
    protected function getInternal($carry, array $pathStack, bool $strict)
    {
        $traveledPath = [];
        $isResultMultiple = false;
        while (count($pathStack)) {
            $key = array_pop($pathStack);
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
                    $pathStack[] = '*';
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
                /** @var iterable<mixed> $carry */
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
     * @param string[] $pathStack
     * @return mixed
     */
    protected function &getRef(array $pathStack)
    {
        $source = &$this->source;

        while (count($pathStack)) {
            $pathItem = array_pop($pathStack);
            $source = &ContainerAccessHelper::getRef($source, $pathItem, []);

            if (count($pathStack) && is_scalar($source)) {
                $source = [];
            }
        }

        return $source;
    }

    /**
     * @param string|string[]|null $path
     * @return string[]
     */
    protected function getPathStack($path): array
    {
        return array_reverse($this->getPathList($path));
    }

    /**
     * @param string|string[]|mixed|null $path
     * @return string[]
     */
    protected function getPathList($path): array
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

        return $path;
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

        throw new PathNotExistException($key, $path, $this->pathDelimiter);
    }
}
