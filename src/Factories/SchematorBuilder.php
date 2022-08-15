<?php

namespace Smoren\Schemator\Factories;

use Smoren\Schemator\Interfaces\SchematorBuilderInterface;
use Smoren\Schemator\Interfaces\SchematorInterface;
use Smoren\Schemator\Components\Schemator;

class SchematorBuilder implements SchematorBuilderInterface
{
    protected SchematorInterface $schemator;

    public function __construct()
    {
        $this->create();
    }

    public function create(): SchematorBuilderInterface
    {
        $this->schemator = new Schemator();
        return $this;
    }

    public function withFilters($filters): SchematorBuilderInterface
    {
        foreach($filters as $filterName => $filter) {
            $this->schemator->addFilter($filterName, $filter);
        }
        return $this;
    }

    public function get(): SchematorInterface
    {
        return $this->schemator;
    }
}
