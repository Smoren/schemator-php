<?php

namespace Smoren\Shemator\Tests\Unit;


use Smoren\ExtendedExceptions\BadDataException;
use Smoren\Shemator\Schemator;

class SchematorTest extends \Codeception\Test\Unit
{
    public function testMain()
    {
        $ex = new Schemator();

        $input = [
            'id' => 100,
            'name' => 'Novgorod',
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
                ],
            ],
            'msk_path' => 'country.capitals.msk',
        ];

        $data = $ex->exec([
            'city_id' => 'id',
            'city_name' => 'name',
            'city_street_names' => 'streets.name',
            'city_street_houses' => 'streets.houses',
            'country_id' => 'country.id',
            'country_name' => 'country.name',
            'country_friends' => 'country.friends',
            'country_friend' => 'country.friends',
            'country_first_capital' => 'country.capitals.msk',
            'country_second_capital' => 'country.capitals.spb',
            'unknown' => 'unknown',
            'unknown_another' => 'country.unknown',
            'unknown_array' => 'streets.unknown',
            'raw' => '',
            'country_data.country_id' => 'country.id',
            'country_data.country_name' => 'country.name',
        ], $input);

        $this->assertEquals(100, $data['city_id']);
        $this->assertEquals('Novgorod', $data['city_name']);
        $this->assertEquals(['Tverskaya', 'Leninskiy', 'Tarusskaya'], $data['city_street_names']);
        $this->assertEquals([[1, 5, 9], [22, 35, 49], [11, 12, 15]], $data['city_street_houses']);
        $this->assertEquals(10, $data['country_id']);
        $this->assertEquals('Russia', $data['country_name']);
        $this->assertEquals(['Kazakhstan', 'Belarus', 'Armenia'], $data['country_friends']);
        $this->assertEquals('Moscow', $data['country_first_capital']);
        $this->assertEquals('St. Petersburg', $data['country_second_capital']);
        $this->assertEquals(null, $data['unknown']);
        $this->assertEquals(null, $data['unknown_another']);
        $this->assertEquals([null, null, null], $data['unknown_array']);
        $this->assertEquals([
            'country_id' => 10,
            'country_name' => 'Russia',
        ], $data['country_data']);

        try {
            $ex->exec([
                'city_street_names' => ['streets.name', ['join', ', ']]
            ], $input);
            $this->assertTrue(false);
        } catch(BadDataException $e) {
            $this->assertEquals(1, $e->getCode());
        }

        $ex->addFilter('implode', function(Schemator $executor, array $source, array $rootSource, string $delimiter) {
            return implode($delimiter, $source);
        });

        $ex->addFilter('explode', function(Schemator $executor, string $source, array $rootSource, string $delimiter) {
            return explode($delimiter, $source);
        });

        $ex->addFilter('startsWith', function(Schemator $executor, array $source, array $rootSource, string $start) {
            return array_filter($source, function(string $candidate) use ($start) {
                return strpos($candidate, $start) === 0;
            });
        });

        $ex->addFilter('byDynamicPath', function(Schemator $executor, string $source, array $rootSource) {
            return $executor->getValue($rootSource, $source);
        });

        $data = $ex->exec([
            'city_street_names' => ['streets.name', ['implode', ', ']]
        ], $input);
        $this->assertEquals('Tverskaya, Leninskiy, Tarusskaya', $data['city_street_names']);

        $data = $ex->exec([
            'city_street_names' => ['streets.name', ['implode', ', '], ['explode', ', ']]
        ], $input);
        $this->assertEquals(['Tverskaya', 'Leninskiy', 'Tarusskaya'], $data['city_street_names']);

        $data = $ex->exec([
            'city_street_names' => ['streets.name', ['startsWith', 'T'], ['implode', ', ']]
        ], $input);
        $this->assertEquals('Tverskaya, Tarusskaya', $data['city_street_names']);

        $data = $ex->exec([
            'msk' => ['msk_path', ['byDynamicPath']]
        ], $input);
        $this->assertEquals('Moscow', $data['msk']);
    }

    public function testSpecificDelimiter()
    {
        $ex = new Schemator('/');

        $input = [
            'id' => 100,
            'name' => 'Novgorod',
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
                ],
            ],
            'msk_path' => 'country/capitals/msk',
        ];

        $data = $ex->exec([
            'city_id' => 'id',
            'city_name' => 'name',
            'city_street_names' => 'streets/name',
            'city_street_houses' => 'streets/houses',
            'country_id' => 'country/id',
            'country_name' => 'country/name',
            'country_friends' => 'country/friends',
            'country_friend' => 'country/friends',
            'country_first_capital' => 'country/capitals/msk',
            'country_second_capital' => 'country/capitals/spb',
            'unknown' => 'unknown',
            'unknown_another' => 'country/unknown',
            'unknown_array' => 'streets/unknown',
            'raw' => '',
            'country_data/country_id' => 'country/id',
            'country_data/country_name' => 'country/name',
        ], $input);

        $this->assertEquals(100, $data['city_id']);
        $this->assertEquals('Novgorod', $data['city_name']);
        $this->assertEquals(['Tverskaya', 'Leninskiy', 'Tarusskaya'], $data['city_street_names']);
        $this->assertEquals([[1, 5, 9], [22, 35, 49], [11, 12, 15]], $data['city_street_houses']);
        $this->assertEquals(10, $data['country_id']);
        $this->assertEquals('Russia', $data['country_name']);
        $this->assertEquals(['Kazakhstan', 'Belarus', 'Armenia'], $data['country_friends']);
        $this->assertEquals('Moscow', $data['country_first_capital']);
        $this->assertEquals('St. Petersburg', $data['country_second_capital']);
        $this->assertEquals(null, $data['unknown']);
        $this->assertEquals(null, $data['unknown_another']);
        $this->assertEquals([null, null, null], $data['unknown_array']);
        $this->assertEquals([
            'country_id' => 10,
            'country_name' => 'Russia',
        ], $data['country_data']);

        try {
            $ex->exec([
                'city_street_names' => ['streets/name', ['join', ', ']]
            ], $input);
            $this->assertTrue(false);
        } catch(BadDataException $e) {
            $this->assertEquals(1, $e->getCode());
        }

        $ex->addFilter('implode', function(Schemator $executor, array $source, array $rootSource, string $delimiter) {
            return implode($delimiter, $source);
        });

        $ex->addFilter('explode', function(Schemator $executor, string $source, array $rootSource, string $delimiter) {
            return explode($delimiter, $source);
        });

        $ex->addFilter('startsWith', function(Schemator $executor, array $source, array $rootSource, string $start) {
            return array_filter($source, function(string $candidate) use ($start) {
                return strpos($candidate, $start) === 0;
            });
        });

        $ex->addFilter('byDynamicPath', function(Schemator $executor, string $source, array $rootSource) {
            return $executor->getValue($rootSource, $source);
        });

        $data = $ex->exec([
            'city_street_names' => ['streets/name', ['implode', ', ']]
        ], $input);
        $this->assertEquals('Tverskaya, Leninskiy, Tarusskaya', $data['city_street_names']);

        $data = $ex->exec([
            'city_street_names' => ['streets/name', ['implode', ', '], ['explode', ', ']]
        ], $input);
        $this->assertEquals(['Tverskaya', 'Leninskiy', 'Tarusskaya'], $data['city_street_names']);

        $data = $ex->exec([
            'city_street_names' => ['streets/name', ['startsWith', 'T'], ['implode', ', ']]
        ], $input);
        $this->assertEquals('Tverskaya, Tarusskaya', $data['city_street_names']);

        $data = $ex->exec([
            'msk' => ['msk_path', ['byDynamicPath']]
        ], $input);
        $this->assertEquals('Moscow', $data['msk']);
    }
}
