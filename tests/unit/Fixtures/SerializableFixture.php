<?php

declare(strict_types=1);

namespace Smoren\Schemator\Tests\Unit\Fixtures;

class SerializableFixture
{
    protected int $val;

    public function __construct(int $val)
    {
        $this->val = $val;
    }

    public function __sleep(): array
    {
        return ['val'];
    }
}
