<?php

namespace Smoren\Schemator\Interfaces;

/**
 * Interface SchematorFactoryInterface
 * @author Smoren <ofigate@gmail.com>
 */
interface SchematorFactoryInterface
{
    /**
     * Creates SchematorInterface instance
     * @param bool $withBaseFilters flag of using base filters
     * @param iterable<string, callable>|FiltersStorageInterface|null $extraFilters extra filters map
     * @return SchematorInterface
     */
    public static function create(
        bool $withBaseFilters = true,
        ?iterable $extraFilters = null
    ): SchematorInterface;

    /**
     * Creates SchematorMassGenerator instance
     * @param bool $withBaseFilters flag of using base filters
     * @param iterable<string, callable>|FiltersStorageInterface|null $extraFilters extra filters map
     * ([filterName => filterCallback])
     * @return MassSchematorInterface
     */
    public static function createMass(
        bool $withBaseFilters = true,
        ?iterable $extraFilters = null
    ): MassSchematorInterface;
}
