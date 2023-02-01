# Schematic data mapper

![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/smoren/schemator)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Smoren/schemator-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Smoren/schemator-php/?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/Smoren/schemator-php/badge.svg?branch=master)](https://coveralls.io/github/Smoren/schemator-php?branch=master)
![Build and test](https://github.com/Smoren/schemator-php/actions/workflows/test_master.yml/badge.svg)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

![Schemator logo](docs/images/schemator-logo.png)

Schematic data mapper is a tool for converting nested data structures
(any compositions of associative arrays, non-associative arrays and objects)
according to the given conversion schema.

## How to install to your project
```
composer require smoren/schemator
```

## Usage

### Simple usage

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
$output = $schemator->convert($input, $schema);

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

### Setting errors level

```php
use Smoren\Schemator\Factories\SchematorFactory;
use Smoren\Schemator\Structs\ErrorsLevelMask;
use Smoren\Schemator\Exceptions\SchematorException;

$input = [
    'some_key' => null,
];
$schema = [
    'my_value' => ['some_key', ['date', 'Y-m-d']],
];

$schemator = SchematorFactory::createBuilder()
    ->withErrorsLevelMask(
        ErrorsLevelMask::nothing()
            ->add([SchematorException::FILTER_ERROR, SchematorException::CANNOT_GET_VALUE])
    )
    ->get();

try {
    $schemator->convert($input, $schema);
} catch(SchematorException $e) {
    echo $e->getMessage(); // filter error: 'date'
}

```

### Using base filters

```php
use Smoren\Schemator\Factories\SchematorFactory;
use Smoren\Schemator\Filters\BaseFiltersStorage;

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
$output = $schemator->convert($input, $schema);

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

### Using smart filter and replace

```php
use Smoren\Schemator\Factories\SchematorFactory;
use Smoren\Schemator\Filters\BaseFiltersStorage;

$schemator = SchematorFactory::create();
$input = [
    'numbers' => [-1, 10, 5, 22, -10, 0, 35, 7, 8, 9, 0],
];

$output = $schemator->convert($input, [
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

$output = $schemator->convert($input, [
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

### Using custom filters

```php
use Smoren\Schemator\Factories\SchematorFactory;
use Smoren\Schemator\Interfaces\FilterContextInterface;

$schemator = SchematorFactory::createBuilder()
    ->withFilters([
        'startsWith' => function(FilterContextInterface $context, string $start) {
            return array_filter($context->getSource(), function(string $candidate) use ($start) {
                return strpos($candidate, $start) === 0;
            });
        },
    ])
    ->get();

$input = [
    'streets' => ['Tverskaya', 'Leninskiy', 'Tarusskaya'],
];

$schema = [
    'street_names' => ['streets', ['startsWith', 'T'], ['implode', ', ']],
];

$output = $schemator->convert($input, $schema);

print_r($output);
/*
Array
(
    [street_names] => Tverskaya, Tarusskaya
)
*/
```

### Mass usage

```php
use Smoren\Schemator\Factories\SchematorFactory;

$massSchemator = SchematorFactory::createMass();

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

## Filters

### const
Sets the value from const param.

Schema:
```php
["value" => [["const", "My const value"]]]
```

Result:
```php
["value" => "My const value"]
```

### sum
Returns the sum of given array.

Given:
```php
["numbers" => [1, 2, 3, 4, 5]]
```

Schema:
```php
["value" => ["numbers", ["sum"]]]
```

Result:
```php
["value" => 15]
```

### average
Returns the average of given array.

Given:
```php
["numbers" => [1, 2, 3, 4, 5]]
```

Schema:
```php
["value" => ["numbers", ["average"]]]
```

Result:
```php
["value" => 3]
```

### date
Returns formatted date from the Unix timestamp given value.

Params:
1. Date format
    * required
    * data type — string
    * example: `d.m.Y H:i:s`
    * [more about formats](https://www.php.net/manual/ru/datetime.format.php)
2. Time zone offset from GMT
    * optional
    * data type — integer
    * default — 0

Given:
```php
["some_date" => 1651481881]
```

Schema:
```php
["value" => ["some_date", ["date", "d.m.Y H:i:s", 3]]]
```

Result:
```php
["value" => "02.05.2022 11:58:01"]
```

### implode
Returns string of imploded items of given array with separator from args list.

params:
1. Separator
    * required
    * data type — string
    * example: `; `

Given:
```php
["numbers" => [1, 2, 3, 4, 5]]
```

Schema:
```php
["value" => ["numbers", ["implode", "; "]]]
```

Result:
```php
["value" => "1; 2; 3; 4; 5"]
```

### explode
Returns array of exploded strings from given string with separator from args list

params:
1. Separator
    * required
    * data type — string
    * example: `; `

Given:
```php
["numbers" => "1; 2; 3; 4; 5"]
```

Schema:
```php
["value" => ["numbers", ["explode", "; "]]]
```

Result:
```php
["value" => ["1", "2", "3", "4", "5"]]
```

### flatten
Returns flat array contains all the dead end leaves of tree array.

Given:
```php
[
   "numbers" => [
      [
         [1, 2, 3],
         [4, 5, 6]
      ],
      [7, 8, 9]
   ],
]
```

Schema:
```php
["value" => ["numbers", ["flatten"]]]
```

Result:
```php
["value" => [1, 2, 3, 4, 5, 6, 7, 8, 9]]
```

### sort
Sorts and returns given array.

Given:
```php
["numbers" => [3, 5, 4, 1, 2]]
```

Schema:
```php
["value" => ["numbers", ["sort"]]]
```

Result:
```php
["value" => [1, 2, 3, 4, 5]]
```

### rsort
Sorts reversely and returns given array.

Given:
```php
["numbers" => [3, 5, 4, 1, 2]]
```

Schema:
```php
["value" => ["numbers", ["sort"]]]
```

Result:
```php
["value" => [5, 4, 3, 2, 1]]
```

### filter
Returns array contains elements from given array, that match the predicates from params list.

Rules:
* Every predicate has such format `["predicate name", ...parans]`.
* Predicates in one filter apply according the "OR" logic.
* To apply "AND" logic use [chain of filters](#Chain-of-filters).
* Available predicates:
    * `["=", 10]` means `value = 10`
    * `[">", 10]` means `value > 10`
    * `[">=", 10]` means `value >= 10`
    * `["<", 10]` means `value < 10`
    * `["<=", 10]` means `value <= 10`
    * `["in", [1, 2]]` means `value = 1 OR value = 2`
    * `["not in", [1, 2]]` means `value != 1 AND value != 2`
    * `["between", 1, 5]` means `1 <= value <= 5`
    * `["between strict", 1, 5]` means `1 < value < 5`

Given:
```php
["numbers" => [-5, -3, -1, 1, 3, 5]]
```

Schema:
```php
[
   "value" => [
      "numbers",
      [
         "filter",
         [[">", 1], ["<", -1]] // value > 1 OR value < -1
      ],
   ],
]
```

Result:
```php
["value" => [-5, -3, 3, 5]]
```

### replace
Returns array of elements from given array with replaces by rules from params list.

Rules:
* Every rule has such format `["value to replace", "rule name", ...params]`.
* Rules in one filter apply according the "OR" logic.
* To apply "AND" logic use [chain of filters](#Chain-of-filters).
* Available rules:
    * `["=", 10]` means `value = 10`
    * `[">", 10]` means `value > 10`
    * `[">=", 10]` means `value >= 10`
    * `["<", 10]` means `value < 10`
    * `["<=", 10]` means `value <= 10`
    * `["in", [1, 2]]` means `value = 1 или value = 2`
    * `["not in", [1, 2]]` means `value != 1 и value != 2`
    * `["between", 1, 5]` means `1 <= value <= 5`
    * `["between strict", 1, 5]` means `1 < value < 5`
    * `["else"]` — no rules matched for value
      _(If rule `else` did not use, by default such values are replaced with `null`)_

Given:
```php
["numbers" => [-5, -3, -1, 1, 3, 5]]
```

Schema:
```php
[
   "value" => [
      "numbers",
      [
         "replace",
         [
            ["positive", ">", 0],
            ["negative", "<", 0],
            ["zero", "else"]
         ],
      ],
   ],
]
```

Result:
```php
["value" => ["negative", "negative", "negative", "zero", "positive", "positive", "positive"]]
```

### Chain of filters

Given:
```php
["numbers" => [-5, -3, -1, 1, 3, 5]]
```

Schema:
```php
[
   "value" => [
      "numbers",
      [
         "filter",
         [[">", 1], ["<", -1]] // (value > 1 OR value < -1)
      ],
      // AND
      [
         "filter",
         [[">=", -3]] // value >= -3
      ],
   ],
]
```

Result:
```php
["value" => [-3, 3, 5]]
```

## Unit testing
```
composer install
composer test-init
composer test
```

## License

Schemator Data Mapper is licensed under the MIT License.
