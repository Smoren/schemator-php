<?php

declare(strict_types=1);

namespace Smoren\Schemator\Exceptions;

class PathNotWritableException extends PathException
{
    /**
     * {@inheritDoc}
     */
    public function __construct(string $key, array $path, string $pathDelimiter)
    {
        parent::__construct($key, $path, $pathDelimiter);
        $this->message = "Cannot create key '{$this->key}' on path '{$this->getPathString()}'";
    }
}
