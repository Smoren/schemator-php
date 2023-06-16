<?php

declare(strict_types=1);

namespace Smoren\Schemator\Tests\Unit\Fixtures;

class ClassWithAccessibleProperties
{
    /**
     * @var int
     */
    public int $publicProperty = 1;
    /**
     * @var int
     */
    protected int $protectedProperty = 3;
    /**
     * @var int
     */
    private int $privateProperty = 5;
    /**
     * @var int
     */
    public int $publicPropertyWithMethodsAccess = 2;
    /**
     * @var int
     */
    protected int $protectedPropertyWithMethodsAccess = 4;
    /**
     * @var int
     */
    private int $privatePropertyWithMethodsAccess = 6;

    public function getPublicPropertyWithMethodsAccess(): int
    {
        return $this->publicPropertyWithMethodsAccess;
    }

    public function setPublicPropertyWithMethodsAccess(int $value): void
    {
        $this->publicPropertyWithMethodsAccess = $value;
    }

    public function getProtectedPropertyWithMethodsAccess(): int
    {
        return $this->protectedPropertyWithMethodsAccess;
    }

    public function setProtectedPropertyWithMethodsAccess(int $value): void
    {
        $this->protectedPropertyWithMethodsAccess = $value;
    }

    public function getPrivatePropertyWithMethodsAccess(): int
    {
        return $this->privatePropertyWithMethodsAccess;
    }

    public function setPrivatePropertyWithMethodsAccess(int $value): void
    {
        $this->privatePropertyWithMethodsAccess = $value;
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
