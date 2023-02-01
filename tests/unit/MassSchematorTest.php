<?php

namespace Smoren\Schemator\Tests\Unit;

use Smoren\Schemator\Components\MassSchemator;
use Smoren\Schemator\Components\Schemator;
use Smoren\Schemator\Exceptions\SchematorException;
use Smoren\Schemator\Factories\SchematorFactory;

class MassSchematorTest extends \Codeception\Test\Unit
{
    /**
     * @throws SchematorException
     */
    public function testSimple()
    {
        $massSchemator = SchematorFactory::createMass();

        $cities = $this->getCities();

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

        $this->assertEquals($expectedResult, $massSchemator->convert($cities, $schema));
    }

    /**
     * @throws SchematorException
     */
    public function testNested()
    {
        $massSchemator = SchematorFactory::createMass();

        $cities = $this->getCities();

        $gen1 = $massSchemator->generate($cities, [
            'city_id' => 'id',
            'city_name' => 'name',
            'city_street_names' => 'streets.name',
            'country_id' => 'country.id',
            'country_name' => 'country.name',
        ]);

        $gen2 = $massSchemator->generate($gen1, [
            'my_city_id' => 'city_id',
            'my_country.my_id' => 'country_id',
            'my_country.my_name' => 'country_name',
        ]);

        $result = [];
        foreach($gen2 as $item) {
            $result[] = $item;
        }

        $expectedResult = [
            [
                'my_city_id' => 100,
                'my_country' => [
                    'my_id' => 10,
                    'my_name' => 'Russia',
                ],
            ],
            [
                'my_city_id' => 101,
                'my_country' => [
                    'my_id' => 10,
                    'my_name' => 'Russia',
                ],
            ],
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function testExtra()
    {
        $massSchemator = SchematorFactory::createMass();
        $cities = $this->getCities();

        $result = $massSchemator->convert($cities, [
            '' => 'name',
        ]);

        $this->assertEquals(['Novgorod', 'Moscow'], $result);
    }

    /**
     * @return array[]
     */
    protected function getCities(): array
    {
        return [
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
    }
}
