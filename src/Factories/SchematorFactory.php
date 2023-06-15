<?php

declare(strict_types=1);

namespace Smoren\Schemator\Factories;

use Smoren\Schemator\Components\MassSchemator;
use Smoren\Schemator\Filters\BaseFiltersStorage;
use Smoren\Schemator\Interfaces\MassSchematorInterface;
use Smoren\Schemator\Interfaces\SchematorBuilderInterface;
use Smoren\Schemator\Interfaces\SchematorInterface;

/**
 * Schemator factory
 */
class SchematorFactory
{
    /**
     * Creates new Schemator instance with default config.
     *
     * @return SchematorInterface
     */
    public static function create(): SchematorInterface
    {
        return static::createBuilder()
            ->withFilters(new BaseFiltersStorage())
            ->get();
    }

    /**
     * Creates SchematorBuilder instance.
     *
     * @return SchematorBuilderInterface
     */
    public static function createBuilder(): SchematorBuilderInterface
    {
        return new SchematorBuilder();
    }

    /**
     * Creates new MassSchemator instance with default config.
     *
     * @return MassSchematorInterface
     */
    public static function createMass(): MassSchematorInterface
    {
        return new MassSchemator(static::create());
    }
}
