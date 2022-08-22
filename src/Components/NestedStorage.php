<?php

namespace Smoren\Schemator\Components;

use Smoren\Schemator\Exceptions\NestedAccessorException;
use Smoren\Schemator\Factories\NestedAccessorFactory;
use Smoren\Schemator\Interfaces\NestedAccessorInterface;

/**
 * Storage with nested accessor interface
 * @author Smoren <ofigate@gmail.com>
 */
class NestedStorage implements NestedAccessorInterface
{
    /**
     * @var array<scalar, mixed> storage
     */
    protected array $storage;
    /**
     * @var NestedAccessorInterface nested accessor
     */
    protected NestedAccessorInterface $nestedAccessor;

    /**
     * @param array<scalar, mixed>|null $storage
     * @param NestedAccessorFactory|null $factory
     */
    public function __construct(?array $storage = null, ?NestedAccessorFactory $factory = null)
    {
        $this->storage = $storage ?? [];
        $this->nestedAccessor = ($factory ?? new NestedAccessorFactory())->create($this->storage);
    }

    /**
     * @inheritDoc
     * @throws NestedAccessorException
     */
    public function get($path, bool $strict = true)
    {
        return $this->nestedAccessor->get($path, $strict);
    }

    /**
     * @inheritDoc
     * @throws NestedAccessorException
     */
    public function set($path, $value, bool $strict = true): self
    {
        $this->nestedAccessor->set($path, $value, $strict);
        return $this;
    }
}