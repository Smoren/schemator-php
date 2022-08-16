<?php

namespace Smoren\Schemator\Factories;

use Smoren\Schemator\Interfaces\SchematorFactoryInterface;
use Smoren\Schemator\Components\MassSchemator;
use Smoren\Schemator\Filters\BaseFiltersStorage;
use Smoren\Schemator\Interfaces\SchematorInterface;

/**
 * Factory class for creating Schemator instance
 * @author Smoren <ofigate@gmail.com>
 */
class SchematorFactory implements SchematorFactoryInterface
{
    /**
     * @inheritDoc
     */
    public static function create(bool $withBaseFilters = true, ?iterable $extraFilters = null): SchematorInterface
    {
        $builder = static::createBuilder();

        if($withBaseFilters) {
            $builder->withFilters(new BaseFiltersStorage());
        }

        if($extraFilters !== null) {
            $builder->withFilters($extraFilters);
        }

        return $builder->get();
    }

    /**
     * @inheritDoc
     * @return MassSchemator
     */
    public static function createMass(
        bool $withBaseFilters = true,
        ?iterable $extraFilters = null
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
