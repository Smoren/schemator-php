<?php

namespace Smoren\Schemator\Interfaces;

/**
 * @template T
 */
interface ProxyInterface
{
    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param mixed $value
     * @return void
     */
    public function setValue($value): void;
}
