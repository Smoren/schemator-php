<?php

namespace Smoren\Schemator\Structs;

use Smoren\Schemator\Interfaces\FilterContextInterface;
use Smoren\Schemator\Interfaces\SchematorInterface;

class FilterContext implements FilterContextInterface
{
    protected SchematorInterface $schemator;
    protected $source;
    protected $rootSource;

    public function __construct(SchematorInterface $schemator, $source, $rootSource)
    {
        $this->schemator = $schemator;
        $this->source = $source;
        $this->rootSource = $rootSource;
    }

    public function getSchemator(): SchematorInterface
    {
        return $this->schemator;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getRootSource()
    {
        return $this->rootSource;
    }
}
