<?php

namespace Smoren\Schemator\Tests\Unit;


use Smoren\Schemator\Exceptions\NestedAccessorException;
use Smoren\Schemator\NestedAccessor;

class NestedAccessorTest extends \Codeception\Test\Unit
{
    public function testRead()
    {
        $input = [
            'id' => 100,
            'name' => 'Novgorod',
            'status' => null,
            'country' => [
                'id' => 10,
                'name' => 'Russia',
                'friends' => ['Kazakhstan', 'Belarus', 'Armenia'],
                'capitals' => [
                    'msk' => 'Moscow',
                    'spb' => 'St. Petersburg',
                ],
            ],
            'streets' => [
                [
                    'id' => 1000,
                    'name' => 'Tverskaya',
                    'houses' => [1, 5, 9],
                ],
                [
                    'id' => 1002,
                    'name' => 'Leninskiy',
                    'houses' => [22, 35, 49],
                ],
                [
                    'id' => 1003,
                    'name' => 'Tarusskaya',
                    'houses' => [11, 12, 15],
                    'unknown' => 'some value',
                ],
            ],
            'msk_path' => 'country.capitals.msk',
        ];

        $accessor = new NestedAccessor($input);

//        $this->assertEquals('Novgorod', $accessor->get('name'));
//        $this->assertEquals('Novgorod', $accessor->get('name', 'Unknown', true));
//        $this->assertEquals('Novgorod', $accessor->get('name', 'Unknown', false));
//
//        $this->assertEquals(null, $accessor->get('name1', null, false));
//        $this->assertEquals('Unknown', $accessor->get('name1', 'Unknown', false));
//        $this->assertEquals('Unknown', $accessor->get('name1', 'Unknown', true));
//
//        try {
//            $accessor->get('name1', null, true);
//            $this->assertTrue(false);
//        } catch(NestedAccessorException $e) {
//            $this->assertEquals(NestedAccessorException::KEY_NOT_FOUND, $e->getCode());
//        }
//
//        $this->assertEquals(null, $accessor->get('status'));
//        $this->assertEquals(null, $accessor->get('status', null, true));
//        $this->assertEquals('Unknown', $accessor->get('status', 'Unknown', false));
//        $this->assertEquals('Unknown', $accessor->get('status', 'Unknown', true));
//
//        $this->assertEquals('Moscow', $accessor->get('country.capitals.msk'));
//        $this->assertEquals('Unknown', $accessor->get('country.capitals.msk1', 'Unknown'));
//        $this->assertEquals(null, $accessor->get('country.capitals.msk1', null, false));
//        try {
//            $accessor->get('country.capitals.msk1');
//            $this->assertTrue(false);
//        } catch(NestedAccessorException $e) {
//            $this->assertEquals(NestedAccessorException::KEY_NOT_FOUND, $e->getCode());
//        }
    }
}
