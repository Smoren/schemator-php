<?php

namespace Smoren\Schemator\Tests\Unit\Fixtures;

class ParentClass
{
    public int $a;
    protected int $b;

    public function __construct(int $a, int $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

    public function toArray(): array
    {
        return [$this->a, $this->b];
    }
}
