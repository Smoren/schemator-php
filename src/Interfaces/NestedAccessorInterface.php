<?php


namespace Smoren\Schemator\Interfaces;


interface NestedAccessorInterface
{
    public function get(string $path, bool $strict = true);
    public function set(string $path, $value);
    public function getSource(): array;
}