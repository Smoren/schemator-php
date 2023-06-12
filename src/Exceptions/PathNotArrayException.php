<?php

namespace Smoren\Schemator\Exceptions;

class PathNotArrayException extends PathException
{
    /**
     * {@inheritDoc}
     */
    public function __construct(string $key, array $path, string $pathDelimiter)
    {
        parent::__construct($key, $path, $pathDelimiter);
        $this->message = "Value by key '{$this->key}' is not an array on path '{$this->getPathString()}'";
    }
}
