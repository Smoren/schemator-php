<?php

namespace Smoren\Schemator\Interfaces;

use Smoren\Schemator\Components\Schemator;

/**
 * Interface SchematorFactoryInterface
 * @author Smoren <ofigate@gmail.com>
 */
interface SchematorFactoryInterface
{
    /**
     * Creates SchematorInterface instance
     * @param int $errorsLevelMask errors level mask
     * @param bool $withBaseFilters flag of using base filters
     * @param iterable<string, callable>|FiltersStorageInterface|null $extraFilters extra filters map
     * @return SchematorInterface
     */
    public static function create(
        int $errorsLevelMask = Schemator::ERRORS_LEVEL_DEFAULT,
        bool $withBaseFilters = true,
        ?iterable $extraFilters = null
    ): SchematorInterface;

    /**
     * Creates SchematorMassGenerator instance
     * @param int $errorsLevelMask errors level mask
     * @param bool $withBaseFilters flag of using base filters
     * @param iterable<string, callable>|FiltersStorageInterface|null $extraFilters extra filters map
     * ([filterName => filterCallback])
     * @return MassSchematorInterface
     */
    public static function createMass(
        int $errorsLevelMask = Schemator::ERRORS_LEVEL_DEFAULT,
        bool $withBaseFilters = true,
        ?iterable $extraFilters = null
    ): MassSchematorInterface;
}
