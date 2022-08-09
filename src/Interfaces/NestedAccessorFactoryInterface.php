<?php


namespace Smoren\Schemator\Interfaces;


interface NestedAccessorFactoryInterface
{
    public static function create(?array &$source, string $pathDelimiter = '.'): NestedAccessorInterface;
}