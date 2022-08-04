<?php

namespace Smoren\Schemator\Tests\Unit;


use Smoren\Schemator\Exceptions\SchematorException;
use Smoren\Schemator\Schemator;
use Smoren\Schemator\MassSchemator;

class MassSchematorTest extends \Codeception\Test\Unit
{
    /**
     * @throws SchematorException
     */
    public function testMain()
    {
        $massSchemator = new MassSchemator(new Schemator());

        $cities = [
            [
                'id' => 100,
                'name' => 'Novgorod',
                'country' => [
                    'id' => 10,
                    'name' => 'Russia',
                ],
                'streets' => [
                    [
                        'id' => 1001,
                        'name' => 'Glavnaya',
                    ],
                    [
                        'id' => 1002,
                        'name' => 'Lenina',
                    ],
                ],
            ],
            [
                'id' => 101,
                'name' => 'Moscow',
                'country' => [
                    'id' => 10,
                    'name' => 'Russia',
                ],
                'streets' => [
                    [
                        'id' => 1003,
                        'name' => 'Tverskaya',
                    ],
                    [
                        'id' => 1004,
                        'name' => 'Tarusskaya',
                    ],
                ],
            ],
        ];

        $schema = [
            'city_id' => 'id',
            'city_name' => 'name',
            'city_street_names' => 'streets.name',
            'country_id' => 'country.id',
            'country_name' => 'country.name',
        ];

        $gen = $massSchemator->generate($cities, $schema);

        $expectedResult = [
            [
                'city_id' => 100,
                'city_name' => 'Novgorod',
                'city_street_names' => ['Glavnaya', 'Lenina'],
                'country_id' => 10,
                'country_name' => 'Russia',
            ],
            [
                'city_id' => 101,
                'city_name' => 'Moscow',
                'city_street_names' => ['Tverskaya', 'Tarusskaya'],
                'country_id' => 10,
                'country_name' => 'Russia',
            ],
        ];

        $i = 0;
        foreach($gen as $item) {
            $this->assertEquals($expectedResult[$i++], $item);
        }

        $this->assertEquals($expectedResult, $massSchemator->exec($cities, $schema));
    }
}
