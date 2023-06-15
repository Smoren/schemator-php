<?php

declare(strict_types=1);

namespace Smoren\Schemator\Exceptions;

class PathNotExistException extends PathException
{
    /**
     * {@inheritDoc}
     */
    public function __construct(string $key, array $path, string $pathDelimiter)
    {
        parent::__construct($key, $path, $pathDelimiter);
        $this->message = "Key '{$this->key}' is not found on path '{$this->getPathString()}'";
    }
}
