<?php

namespace Smoren\Schemator\Structs;

use Smoren\Schemator\Interfaces\FilterContextInterface;
use Smoren\Schemator\Interfaces\SchematorInterface;

/**
 * Class FilterContext
 * @author Smoren <ofigate@gmail.com>
 */
class FilterContext implements FilterContextInterface
{
    /**
     * @var SchematorInterface Schemator object
     */
    protected SchematorInterface $schemator;
    /**
     * @var mixed source data to apply filter to
     */
    protected $source;
    /**
     * @var mixed root source of Schemator conversion
     */
    protected $rootSource;
    /**
     * @var mixed filter config
     */
    protected $config;

    /**
     * FilterContext constructor.
     * @param SchematorInterface $schemator Schemator object
     * @param mixed $source source data to apply filter to
     * @param mixed $rootSource root source of Schemator conversion
     * @param mixed $config filter config
     */
    public function __construct(SchematorInterface $schemator, $source, $rootSource, $config)
    {
        $this->schemator = $schemator;
        $this->source = $source;
        $this->rootSource = $rootSource;
        $this->config = $config;
    }

    /**
     * Schemator getter
     * @return SchematorInterface
     */
    public function getSchemator(): SchematorInterface
    {
        return $this->schemator;
    }

    /**
     * Source getter
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Root source getter
     * @return mixed
     */
    public function getRootSource()
    {
        return $this->rootSource;
    }

    /**
     * Config getter
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }
}
