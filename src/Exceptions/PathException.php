<?php

namespace Smoren\Schemator\Exceptions;

abstract class PathException extends \OutOfBoundsException
{
    /**
     * @var string
     */
    protected string $key;
    /**
     * @var string[]
     */
    protected array $path;
    /**
     * @var non-empty-string
     */
    protected string $pathDelimiter;

    /**
     * @param string $key
     * @param string[] $path
     * @param non-empty-string $pathDelimiter
     */
    public function __construct(string $key, array $path, string $pathDelimiter)
    {
        parent::__construct();
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
        return implode($this->pathDelimiter, $this->getPath());
    }
}
