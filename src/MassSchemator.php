<?php


namespace Smoren\Schemator;


use Generator;

/**
 * Class for mass schematic data converting
 * @package Smoren\Schemator
 */
class MassSchemator
{
    /**
     * @var Schemator Schemator instance
     */
    protected Schemator $schemator;

    /**
     * MassSchemator constructor.
     * @param Schemator $schemator Schemator instance
     */
    public function __construct(Schemator $schemator)
    {
        $this->schemator = $schemator;
    }

    /**
     * Makes a generator for converting every item in the source array by schemator
     * @param array $source source array of items to convert every one by schemator
     * @param array $schema schema for converting
     * @return Generator
     * @throws Exceptions\SchematorException
     */
    public function generate(array $source, array $schema): Generator
    {
        foreach($source as $item) {
            yield $this->schemator->exec($item, $schema);
        }
    }

    /**
     * Converts input data array with using schema
     * @param array $source source array of items to convert every one by schemator
     * @param array $schema schema for converting
     * @return array array of converted items
     * @throws Exceptions\SchematorException
     */
    public function exec(array $source, array $schema): array
    {
        $gen = $this->generate($source, $schema);
        $result = [];

        foreach($gen as $item) {
            $result[] = $item;
        }

        return $result;
    }
}
