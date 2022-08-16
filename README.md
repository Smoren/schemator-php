# schemator

![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/smoren/schemator)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Smoren/schemator-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Smoren/schemator-php/?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/Smoren/schemator-php/badge.svg?branch=master)](https://coveralls.io/github/Smoren/schemator-php?branch=master)
![Build and test](https://github.com/Smoren/schemator-php/actions/workflows/test.yml/badge.svg)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Schematic data converter — tool for converting nested data structures
(associative, non-associative arrays and objects compositions) by given 
conversion schema.

### How to install to your project
```
composer require smoren/schemator
```

### Unit testing
```
composer install
composer test-init
composer test
```

### Usage

#### Simple usage

```php
use Smoren\Schemator\Factories\SchematorFactory;

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

$schema = [
    'city_id' => 'id',
    'city_name' => 'name',
    'city_street_names' => 'streets.name',
    'country_id' => 'country.id',
    'country_name' => 'country.name',
    'country_friends' => 'country.friends',
    'country_friend' => 'country.friends',
    'country_first_capital' => 'country.capitals.msk',
    'country_second_capital' => 'country.capitals.spb',
    'country_data.country_id' => 'country.id',
    'country_data.country_name' => 'country.name',
];

$schemator = SchematorFactory::create();
$output = $schemator->exec($input, $schema);

print_r($output);
/* Array
(
    [city_id] => 100
    [city_name] => Novgorod
    [city_street_names] => Array
        (
            [0] => Tverskaya
            [1] => Leninskiy
            [2] => Tarusskaya
        )

    [country_id] => 10
    [country_name] => Russia
    [country_friends] => Array
        (
            [0] => Kazakhstan
            [1] => Belarus
            [2] => Armenia
        )

    [country_friend] => Array
        (
            [0] => Kazakhstan
            [1] => Belarus
            [2] => Armenia
        )

    [country_first_capital] => Moscow
    [country_second_capital] => St. Petersburg
    [country_data] => Array
        (
            [country_id] => 10
            [country_name] => Russia
        )

)
*/
```

#### Using base filters

```php
use Smoren\Schemator\Factories\SchematorFactory;

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

$schema = [
    'city_street_names.all' => ['streets.name', ['implode', ', ']],
    'city_street_names.sorted' => ['streets.name', ['sort'], ['implode', ', ']],
    'city_street_names.filtered' => ['streets.name', ['filter', function(string $candidate) {
        return strpos($candidate, 'Len') !== false;
    }]],
    'msk' => ['msk_path', ['path']],
    'city_street_houses' => ['streets.houses', ['flatten']],
];

$schemator = SchematorFactory::create();
$output = $schemator->exec($input, $schema);

print_r($output);
/*
Array
(
    [city_street_names] => Array
        (
            [all] => Tverskaya, Leninskiy, Tarusskaya
            [sorted] => Leninskiy, Tarusskaya, Tverskaya
            [filtered] => Array
                (
                    [0] => Leninskiy
                )

        )

    [msk] => Moscow
    [city_street_houses] => Array
        (
            [0] => 1
            [1] => 5
            [2] => 9
            [3] => 22
            [4] => 35
            [5] => 49
            [6] => 11
            [7] => 12
            [8] => 15
        )

)
*/
```

#### Using smart filter and replace

```php
use Smoren\Schemator\Factories\SchematorFactory;

$schemator = SchematorFactory::create();
$input = [
    'numbers' => [-1, 10, 5, 22, -10, 0, 35, 7, 8, 9, 0],
];

$output = $schemator->exec($input, [
    'positive' => [
        'numbers',
        ['filter', [['>', 0]]],
        ['sort'],
    ],
    'negative' => [
        'numbers',
        ['filter', [['<', 0]]],
        ['sort'],
    ],
    'complicated' => [
        'numbers',
        ['filter', [['>=', 8], ['<', 0]]],
        ['filter', [['<', 22]]],
        ['sort'],
    ],
]);

print_r($output);
/*
Array
(
    [positive] => Array
        (
            [0] => 5
            [1] => 7
            [2] => 8
            [3] => 9
            [4] => 10
            [5] => 22
            [6] => 35
        )

    [negative] => Array
        (
            [0] => -10
            [1] => -1
        )

    [complicated] => Array
        (
            [0] => -10
            [1] => -1
            [2] => 8
            [3] => 9
            [4] => 10
        )

)
*/

$output = $schemator->exec($input, [
    'number_types' => ['numbers', [
        'replace',
        [
            ['=0', '=', 0],
            ['>9', '>', 9],
            ['<0', '<', 0],
            ['1-8', 'between', 1, 8],
        ]
    ]]
]);

print_r($output);
/*
Array
(
    [number_types] => Array
        (
            [0] => <0
            [1] => >9
            [2] => 1-8
            [3] => >9
            [4] => <0
            [5] => =0
            [6] => >9
            [7] => 1-8
            [8] => 1-8
            [9] => 9
            [10] => =0
        )

)
*/
```

#### Using custom filters

```php
use Smoren\Schemator\Factories\SchematorFactory;
use Smoren\Schemator\Interfaces\FilterContextInterface;

$schemator = SchematorFactory::create(true, [
    'startsWith' => function(FilterContextInterface $context, string $start) {
        return array_filter($context->getSource(), function(string $candidate) use ($start) {
            return strpos($candidate, $start) === 0;
        });
    },
]);

$input = [
    'streets' => ['Tverskaya', 'Leninskiy', 'Tarusskaya'],
];

$schema = [
    'street_names' => ['streets', ['startsWith', 'T'], ['implode', ', ']],
];

$output = $schemator->exec($input, $schema);

print_r($output);
/*
Array
(
    [street_names] => Tverskaya, Tarusskaya
)
*/
```

#### Mass usage

```php
use Smoren\Schemator\Components\Schemator;
use Smoren\Schemator\Components\MassSchemator;

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

$result = [];
foreach($gen as $item) {
    $result[] = $item;
}

print_r($result);
/*
Array
(
    [0] => Array
        (
            [city_id] => 100
            [city_name] => Novgorod
            [city_street_names] => Array
                (
                    [0] => Glavnaya
                    [1] => Lenina
                )

            [country_id] => 10
            [country_name] => Russia
        )
    [1] => Array
        (
            [city_id] => 101
            [city_name] => Moscow
            [city_street_names] => Array
                (
                    [0] => Tverskaya
                    [1] => Tarusskaya
                )

            [country_id] => 10
            [country_name] => Russia
        )
)
*/
```
