<?php

namespace Smoren\Schemator\Interfaces;

interface FilterContextInterface
{
    public function getSource();
    public function getRootSource();
    public function getSchemator(): SchematorInterface;
}
