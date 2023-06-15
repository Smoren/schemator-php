<?php

namespace Smoren\Schemator\Tests\Unit\Fixtures;

class ClassWithAccessibleProperties
{
    public int $publicProperty = 1;
    public int $publicPropertyWithGetterAccess = 2;
    protected int $protectedProperty = 3;
    protected int $protectedPropertyWithGetterAccess = 4;
    private int $privateProperty = 5;
    private int $privatePropertyWithGetterAccess = 6;

    public function getPublicPropertyWithGetterAccess(): int
    {
        return $this->publicPropertyWithGetterAccess;
    }

    public function setPublicPropertyWithGetterAccess(int $value): void
    {
        $this->publicPropertyWithGetterAccess = $value;
    }

    public function getProtectedPropertyWithGetterAccess(): int
    {
        return $this->protectedPropertyWithGetterAccess;
    }

    public function setProtectedPropertyWithGetterAccess(int $value): void
    {
        $this->protectedPropertyWithGetterAccess = $value;
    }

    public function getPrivatePropertyWithGetterAccess(): int
    {
        return $this->privatePropertyWithGetterAccess;
    }

    public function setPrivatePropertyWithGetterAccess(int $value): void
    {
        $this->privatePropertyWithGetterAccess = $value;
    }

    protected function protectedMethod(): int
    {
        return 100;
    }

    protected function privateMethod(): int
    {
        return 1000;
    }
}
