<?php

namespace Smoren\Schemator\Factories;

use Smoren\Helpers\ArrHelper;
use Smoren\Helpers\RuleHelper;
use Smoren\Schemator\Components\MassSchemator;
use Smoren\Schemator\Components\Schemator;
use Smoren\Schemator\Filters\BaseFilters;
use Smoren\Schemator\Interfaces\SchematorFactoryInterface;

/**
 * Factory class for creating Schemator instance
 * @author Smoren <ofigate@gmail.com>
 */
class SchematorFactory implements SchematorFactoryInterface
{
    /**
     * Creates Schemator instance
     * @param bool $withBaseFilters flag of using base filters
     * @param callable[] $extraFilters extra filters map ([filterName => filterCallback])
     * @return Schemator
     */
    public static function create(bool $withBaseFilters = true, array $extraFilters = []): Schemator
    {
        $builder = static::createBuilder();

        if($withBaseFilters) {
            $builder->withFilters(BaseFilters::get());
        }

        if(count($extraFilters)) {
            $builder->withFilters($extraFilters);
        }

        return $builder->get();
    }

    /**
     * Creates SchematorMassGenerator instance
     * @param bool $withBaseFilters flag of using base filters
     * @param callable[] $extraFilters extra filters map ([filterName => filterCallback])
     * @return MassSchemator
     */
    public static function createMass(
        bool $withBaseFilters = true,
        array $extraFilters = []
    ): MassSchemator {
        return new MassSchemator(static::create($withBaseFilters, $extraFilters));
    }

    /**
     * Creates SchematorBuilder instance
     * @return SchematorBuilder
     */
    protected static function createBuilder(): SchematorBuilder
    {
        return new SchematorBuilder();
    }
}
