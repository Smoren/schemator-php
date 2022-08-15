<?php

namespace Smoren\Schemator\Filters;

use Smoren\Helpers\ArrHelper;
use Smoren\Helpers\RuleHelper;
use Smoren\Schemator\Components\Schemator;
use Smoren\Schemator\Interfaces\FilterStorageInterface;

class BaseFilters implements FilterStorageInterface
{
    /**
     * @inheritDoc
     */
    public static function get(): array
    {
        return [
            'const' => function(
                Schemator $schemator,
                $source,
                array $rootSource,
                $constValue
            ) {
                return $constValue;
            },
            'format' => function(
                Schemator $schemator,
                $source,
                array $rootSource,
                callable $formatter,
                ...$args
            ) {
                return $formatter($source, ...$args);
            },
            'date' => function(
                Schemator $schemator,
                ?int $source,
                array $rootSource,
                string $format,
                ?int $timezone = null
            ) {
                if($source === null) {
                    return null;
                }
                if($timezone === null) {
                    return date($format, $source);
                }
                return gmdate($format, $source+3600*$timezone);
            },
            'implode' => function(
                Schemator $schemator,
                ?array $source,
                array $rootSource,
                string $delimiter = ', '
            ) {
                if($source === null) {
                    return null;
                }
                return implode($delimiter, $source);
            },
            'explode' => function(
                Schemator $schemator,
                ?string $source,
                array $rootSource,
                string $delimiter = ', '
            ) {
                if($source === null) {
                    return null;
                }
                return explode($delimiter, $source);
            },
            'sum' => function(
                Schemator $schemator,
                ?array $source
            ) {
                if($source === null) {
                    return null;
                }
                return array_sum($source);
            },
            'average' => function(
                Schemator $schemator,
                ?array $source
            ) {
                if($source === null) {
                    return null;
                }
                return array_sum($source)/count($source);
            },
            'filter' => function(
                Schemator $schemator,
                ?array $source,
                array $rootSource,
                $filterConfig
            ) {
                if($source === null) {
                    return null;
                }

                if(is_callable($filterConfig)) {
                    return array_values(array_filter($source, $filterConfig));
                }

                $result = [];

                foreach($source as $item) {
                    foreach($filterConfig as $args) {
                        $rule = array_shift($args);

                        if(RuleHelper::check($item, $rule, $args)) {
                            $result[] = $item;
                            break;
                        }
                    }
                }

                return $result;
            },
            'sort' => function(
                Schemator $schemator,
                ?array $source,
                array $rootSource,
                ?callable $sortCallback = null
            ) {
                if($source === null) {
                    return null;
                }
                if($sortCallback !== null) {
                    usort($source, $sortCallback);
                } else {
                    sort($source);
                }
                return $source;
            },
            'rsort' => function(
                Schemator $schemator,
                ?array $source
            ) {
                if($source === null) {
                    return null;
                }

                rsort($source);
                return $source;
            },
            'path' => function(
                Schemator $schemator,
                ?string $source,
                array $rootSource
            ) {
                if($source === null) {
                    return null;
                }
                return $schemator->getValue($rootSource, $source);
            },
            'flatten' => function(
                Schemator $schemator,
                ?array $source
            ) {
                if($source === null) {
                    return null;
                }
                return ArrHelper::flatten($source);
            },
            'replace' => function(
                Schemator $schemator,
                $source,
                array $rootSource,
                array $rules
            ) {
                if($source === null) {
                    return null;
                }

                $isArray = is_array($source);

                if(!$isArray) {
                    $source = [$source];
                }

                $result = [];

                foreach($source as $item) {
                    $isReplaced = false;
                    $elseValue = $item;

                    foreach($rules as $args) {
                        $value = array_shift($args);
                        $rule = array_shift($args);

                        if($rule === 'else') {
                            $elseValue = $value;
                        }

                        $replace = null;

                        if(RuleHelper::check($item, $rule, $args)) {
                            $replace = $value;
                            $isReplaced = true;

                            $result[] = $replace;
                            break;
                        }
                    }

                    if(!$isReplaced) {
                        $result[] = $elseValue;
                    }
                }

                if(!$isArray) {
                    $result = $result[0];
                }

                return $result;
            },
        ];
    }
}
