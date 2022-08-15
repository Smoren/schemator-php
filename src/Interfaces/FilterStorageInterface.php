<?php

namespace Smoren\Schemator\Interfaces;

interface FilterStorageInterface
{
    /**
     * @return callable[]
     */
    public static function get(): array;
}
