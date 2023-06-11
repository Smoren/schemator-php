<?php

namespace Smoren\Schemator\Exceptions;

class PathException extends \OutOfBoundsException
{
    protected string $key;
    protected array $path;
    protected string $pathDelimiter;

    /**
     * @param string $key
     * @param array $path
     * @param string $pathDelimiter
     */
    public function __construct(string $key, array $path, string $pathDelimiter)
    {
        $this->key = $key;
        $this->path = $path;
        $this->pathDelimiter = $pathDelimiter;
        parent::__construct("Key '{$this->key}' is not found on path '{$this->getPathString()}'");
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string[]
     */
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getPathString(): string
    {
        return implode($this->pathDelimiter, $this->path);
    }
}
