<?php

namespace Smoren\Schemator\Factories;

use Smoren\Schemator\Interfaces\BitmapInterface;
use Smoren\Schemator\Interfaces\SchematorBuilderInterface;
use Smoren\Schemator\Interfaces\SchematorInterface;
use Smoren\Schemator\Components\Schemator;

/**
 * Builder of Schemator object
 * @author Smoren <ofigate@gmail.com>
 */
class SchematorBuilder implements SchematorBuilderInterface
{
    /**
     * @var Schemator Schemator object
     */
    protected SchematorInterface $schemator;

    /**
     * SchematorBuilder constructor.
     */
    public function __construct()
    {
        $this->create();
    }

    /**
     * @inheritDoc
     */
    public function create(): SchematorBuilder
    {
        $this->schemator = new Schemator();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withPathDelimiter(string $pathDelimiter): SchematorBuilder
    {
        $this->schemator->setPathDelimiter($pathDelimiter);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withErrorsLevelMask(BitmapInterface $errorsLevelMask): SchematorBuilder
    {
        $this->schemator->setErrorsLevelMask($errorsLevelMask);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withFilters(iterable $filters): SchematorBuilder
    {
        foreach ($filters as $filterName => $filter) {
            $this->schemator->addFilter($filterName, $filter);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function get(): Schemator
    {
        return $this->schemator;
    }
}
