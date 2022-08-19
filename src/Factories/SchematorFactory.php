<?php

namespace Smoren\Schemator\Factories;

use Smoren\Schemator\Components\Schemator;
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
    public static function create(
        int $errorsLevelMask = Schemator::ERRORS_LEVEL_DEFAULT,
        bool $withBaseFilters = true,
        ?iterable $extraFilters = null
    ): SchematorInterface {
        $builder = static::createBuilder();

        $builder->withErrorsLevelMask($errorsLevelMask);

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
        int $errorsLevelMask = Schemator::ERRORS_LEVEL_DEFAULT,
        bool $withBaseFilters = true,
        ?iterable $extraFilters = null
    ): MassSchemator {
        return new MassSchemator(static::create($errorsLevelMask, $withBaseFilters, $extraFilters));
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
