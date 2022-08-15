<?php

namespace Smoren\Schemator\Interfaces;

interface SchematorBuilderInterface
{
    public function create(): SchematorBuilderInterface;
    public function withFilters(array $filters): SchematorBuilderInterface;
    public function get(): SchematorInterface;
}
