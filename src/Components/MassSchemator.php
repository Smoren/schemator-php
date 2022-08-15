<?php

namespace Smoren\Schemator\Components;

use Smoren\Schemator\Interfaces\MassSchematorInterface;
use Smoren\Schemator\Exceptions\SchematorException;
use Generator;

/**
 * Class for mass schematic data converting
 * @author Smoren <ofigate@gmail.com>
 */
class MassSchemator implements MassSchematorInterface
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
     * @param iterable $source iterable source of items to convert every one by schemator
     * @param array $schema schema for converting
     * @return Generator
     * @throws SchematorException
     */
    public function generate(iterable $source, array $schema): Generator
    {
        foreach($source as $item) {
            yield $this->schemator->exec($item, $schema);
        }
    }

    /**
     * Converts input data array with using schema
     * @param iterable $source iterable source of items to convert every one by schemator
     * @param array $schema schema for converting
     * @return array array of converted items
     * @throws SchematorException
     */
    public function exec(iterable $source, array $schema): array
    {
        $gen = $this->generate($source, $schema);
        $result = [];

        foreach($gen as $item) {
            $result[] = $item;
        }

        return $result;
    }
}
