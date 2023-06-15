<?php

namespace Smoren\Schemator\Tests\Unit\Fixtures;

class IndependentClass
{
    public int $a;
    protected int $b;
    protected $c = null;

    public function __construct(int $a, int $b, ?int $c)
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }

    public function toArray(): array
    {
        return [$this->a, $this->b, $this->c];
    }
}
