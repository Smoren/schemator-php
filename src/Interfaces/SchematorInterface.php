<?php


namespace Smoren\Schemator\Interfaces;


interface SchematorInterface
{
    public function exec(array $source, array $schema, bool $strict = false);
    public function getValue(?array $source, $key, bool $strict = false);
    public function addFilter(string $filterName, callable $callback): self;
}
