<?php

declare(strict_types=1);

namespace Smoren\Schemator\Exceptions;

class PathNotArrayAccessibleException extends PathException
{
    /**
     * {@inheritDoc}
     */
    public function __construct(string $key, array $path, string $pathDelimiter)
    {
        parent::__construct($key, $path, $pathDelimiter);
        $this->message = "Value by key '{$this->key}' is not array accessible in path '{$this->getPathString()}'";
    }
}
