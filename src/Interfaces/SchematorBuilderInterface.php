<?php

declare(strict_types=1);

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
     * Sets path delimiter
     * @param non-empty-string $pathDelimiter path delimiter
     * @return SchematorBuilderInterface
     */
    public function withPathDelimiter(string $pathDelimiter): SchematorBuilderInterface;

    /**
     * Sets errors level mask
     * @param BitmapInterface $errorsLevelMask errors level mask
     * @return SchematorBuilderInterface
     */
    public function withErrorsLevelMask(BitmapInterface $errorsLevelMask): SchematorBuilderInterface;

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
