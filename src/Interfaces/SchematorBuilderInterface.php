<?php

namespace Smoren\Schemator\Interfaces;

interface SchematorBuilderInterface
{
    /**
     * @return SchematorBuilderInterface
     */
    public function create(): SchematorBuilderInterface;

    /**
     * @param array|FiltersStorageInterface $filters
     * @return SchematorBuilderInterface
     */
    public function withFilters(iterable $filters): SchematorBuilderInterface;

    /**
     * @return SchematorInterface
     */
    public function get(): SchematorInterface;
}
