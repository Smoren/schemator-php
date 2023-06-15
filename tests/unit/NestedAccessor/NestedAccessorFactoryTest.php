<?php

namespace Smoren\Schemator\Tests\Unit\NestedAccessor;

use Smoren\Schemator\Components\NestedAccessor;
use Smoren\Schemator\Factories\NestedAccessorFactory;

class NestedAccessorFactoryTest extends \Codeception\Test\Unit
{
    /**
     * @throws NestedAccessorException
     */
    public function testExplicitFactory()
    {
        $sourceArray = ['test' => 1];
        $na = NestedAccessorFactory::create($sourceArray);
        $this->assertEquals(1, $na->get('test'));

        $na = NestedAccessorFactory::fromArray($sourceArray);
        $this->assertEquals(1, $na->get('test'));

        $sourceObject = (object)$sourceArray;
        $na = NestedAccessorFactory::fromObject($sourceObject);
        $this->assertEquals(1, $na->get('test'));
    }
}
