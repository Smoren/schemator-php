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

        $this->assertEquals('Novgorod', $accessor->get('name'));
        $this->assertEquals('Novgorod', $accessor->get('name', true));
        $this->assertEquals('Novgorod', $accessor->get('name', false));

        $this->assertEquals(null, $accessor->get('name1', false));

        try {
            $accessor->get('name1', true);
            $this->assertTrue(false);
        } catch(NestedAccessorException $e) {
            $this->assertEquals(NestedAccessorException::KEYS_NOT_FOUND, $e->getCode());
        }

        try {
            $accessor->get('name1');
            $this->assertTrue(false);
        } catch(NestedAccessorException $e) {
            $this->assertEquals(NestedAccessorException::KEYS_NOT_FOUND, $e->getCode());
        }

        try {
            $accessor->get('name1', true);
            $this->assertTrue(false);
        } catch(NestedAccessorException $e) {
            $this->assertEquals(NestedAccessorException::KEYS_NOT_FOUND, $e->getCode());
        }

        $this->assertEquals(null, $accessor->get('status'));
        $this->assertEquals(null, $accessor->get('status', true));
        $this->assertEquals(null, $accessor->get('status', false));

        $this->assertEquals('Moscow', $accessor->get('country.capitals.msk'));
        $this->assertEquals(null, $accessor->get('country.capitals.msk1', false));
        try {
            $accessor->get('country.capitals.msk1');
            $this->assertTrue(false);
        } catch(NestedAccessorException $e) {
            $this->assertEquals(NestedAccessorException::KEYS_NOT_FOUND, $e->getCode());
        }
    }
}
