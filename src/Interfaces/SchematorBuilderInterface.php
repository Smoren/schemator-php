<?php

namespace Smoren\Schemator\Interfaces;

/**
 * Interface SchematorBuilderInterface
 * @author Smoren <ofigate@gmail.com>
 */
interface SchematorBuilderInterface
{
    /**
     * Creates the SchematorInterface instance
     * @return SchematorBuilderInterface
     */
    public function create(): SchematorBuilderInterface;

    /**
     * Adds filters to SchematorInterface object
     * @param array<string, callable>|FiltersStorageInterface $filters
     * @return SchematorBuilderInterface
     */
    public function withFilters(iterable $filters): SchematorBuilderInterface;

    /**
     * Returns SchematorInterface object
     * @return SchematorInterface
     */
    public function get(): SchematorInterface;
}
