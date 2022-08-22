<?php

namespace Smoren\Schemator\Interfaces;

/**
 * Interface FilterContextInterface
 * @author Smoren <ofigate@gmail.com>
 */
interface FilterContextInterface
{
    /**
     * Returns the source data to apply filter to
     * @return mixed
     */
    public function getSource();

    /**, $source
     * Return the root source data of schemator conversion
     * @return mixed
     */
    public function getRootSource();

    /**
     * Returns the schemator instance
     * @return SchematorInterface
     */
    public function getSchemator(): SchematorInterface;

    /**
     * Returns filter config
     * @return mixed
     */
    public function getConfig();

    /**
     * Returns filter name
     * @return string
     */
    public function getFilterName(): string;
}
