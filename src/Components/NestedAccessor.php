<?php

namespace Smoren\Schemator\Components;

use Smoren\Schemator\Exceptions\PathNotArrayException;
use Smoren\Schemator\Exceptions\PathNotExistException;
use Smoren\Schemator\Helpers\ContainerAccessHelper;
use Smoren\Schemator\Interfaces\NestedAccessorInterface;

/**
 * @implements NestedAccessorInterface<string|string[]|null>
 */
class NestedAccessor implements NestedAccessorInterface
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
     * NestedAccessor contructor.
     *
     * @param array<mixed>|object $source
     * @param non-empty-string $pathDelimiter
     */
    public function __construct(&$source, string $pathDelimiter = '.')
    {
        $this->source = &$source;
        $this->pathDelimiter = $pathDelimiter;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function get($path = null, bool $strict = true)
    {
        $carry = $this->source;
        $pathStack = $this->getPathStack($path);
        $traveledPath = [];
        $isResultMultiple = false;

        while (count($pathStack)) {
            $key = array_pop($pathStack);
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

                if ($isResultMultiple) {
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

            if ($isResultMultiple) {
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
     * {@inheritDoc}
     */
    public function set($path, $value): self
    {
        $source = &$this->getRef($this->getPathStack($path));
        $source = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function update($path, $value): self
    {
        $this->checkExist($path);

        return $this->set($path, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function append($path, $value): self
    {
        $this->checkIsArrayAccessible($path);

        /** @var array<mixed> $source */
        $source = &$this->getRef($this->getPathStack($path));
        $source[] = $value;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($path, bool $strict = true): self
    {
        $strict && $this->checkExist($path);

        [$key, $path] = $this->cutPathTail($path);
        $source = &$this->getRef($this->getPathStack($path));
        ContainerAccessHelper::delete($source, $key);
        return $this;
    }

    /**
     * Checks if given path exist in container.
     *
     * @param string|string[]|null $path
     *
     * @throws PathNotExistException when path does not exist in container.
     * @throws \InvalidArgumentException when invalid path passed.
     */
    protected function checkExist($path): void
    {
        $this->get($path);
    }

    /**
     * Check if value by given path is array or ArrayAccess instance.
     *
     * @param string|string[]|null $path
     *
     * @return void
     *
     * @throws PathNotArrayException if path is not an array or ArrayAccess instance.
     * @throws \InvalidArgumentException when invalid path passed.
     */
    protected function checkIsArrayAccessible($path): void
    {
        if (!$this->exist($path) || !ContainerAccessHelper::isArrayAccessible($this->get($path))) {
            [$key, $path] = $this->cutPathTail($path);
            throw new PathNotArrayException($key, $path, $this->pathDelimiter);
        }
    }

    /**
     * Cuts last key from given path.
     *
     * Returns array of last key and truncated path.
     *
     * @param string|string[]|null $path
     *
     * @return array{string, string[]} [lastKey, truncatedPath]
     *
     * @throws \InvalidArgumentException when invalid path passed.
     */
    protected function cutPathTail($path): array
    {
        $path = $this->getPathList($path);
        return [strval(array_pop($path)), $path];
    }

    /**
     * Returns ref to value stored by given path.
     *
     * Creates path if it does not exist.
     *
     * @param string[] $pathStack
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException when invalid path passed.
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
     * Converts given path to stack array.
     *
     * @param string|string[]|null $path
     *
     * @return string[]
     *
     * @throws \InvalidArgumentException when invalid path passed.
     */
    protected function getPathStack($path): array
    {
        return array_reverse($this->getPathList($path));
    }

    /**
     * Converts given path to array.
     *
     * @param string|string[]|mixed|null $path
     *
     * @return string[]
     *
     * @throws \InvalidArgumentException when invalid path passed.
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
     * Handle path errors.
     *
     * @param string $key
     * @param string[] $path
     * @param bool $isResultMultiple
     * @param bool $strict
     *
     * @return null|array{}
     *
     * @throws PathNotExistException always in strict mode.
     */
    protected function handleError(string $key, array $path, bool $isResultMultiple, bool $strict): ?array
    {
        if (!$strict) {
            return $isResultMultiple ? [] : null;
        }

        throw new PathNotExistException($key, $path, $this->pathDelimiter);
    }
}
