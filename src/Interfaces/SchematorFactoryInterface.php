<?php

namespace Smoren\Schemator\Interfaces;

interface SchematorFactoryInterface
{
    /**
     * Creates SchematorInterface instance
     * @param bool $withBaseFilters flag of using base filters
     * @param callable[] $extraFilters extra filters map ([filterName => filterCallback])
     * @return SchematorInterface
     */
    public static function create(
        bool $withBaseFilters = true,
        array $extraFilters = []
    ): SchematorInterface;

    /**
     * Creates SchematorMassGenerator instance
     * @param bool $withBaseFilters flag of using base filters
     * @param callable[] $extraFilters extra filters map ([filterName => filterCallback])
     * @return MassSchematorInterface
     */
    public static function createMass(
        bool $withBaseFilters = true,
        array $extraFilters = []
    ): MassSchematorInterface;
}
