<?php


namespace Smoren\Schemator;

use Smoren\Helpers\ArrHelper;
use Smoren\Helpers\RuleHelper;

/**
 * Factory class for creating Schemator instance
 * @author Smoren <ofigate@gmail.com>
 */
class SchematorFactory
{
    /**
     * Creates Schemator instance
     * @param bool $withBaseFilters flag of using base filters
     * @param callable[] $extraFilters extra filters map ([filterName => filterCallback])
     * @return Schemator
     */
    public static function create(bool $withBaseFilters = true, array $extraFilters = []): Schemator
    {
        $schemator = new Schemator();

        if($withBaseFilters) {
            static::addBaseFilters($schemator);
        }

        foreach($extraFilters as $filterName => $filterCallback) {
            $schemator->addFilter($filterName, $filterCallback);
        }

        return $schemator;
    }

    /**
     * Adds base filters to Schemator instance
     * @param Schemator $schemator instance
     */
    public static function addBaseFilters(Schemator $schemator)
    {
        $schemator->addFilter(
            'const',
            function(Schemator $schemator, $source, array $rootSource, $constValue) {
                return $constValue;
            }
        );

        $schemator->addFilter(
            'format',
            function(Schemator $schemator, $source, array $rootSource, callable $formatter, ...$args) {
                return $formatter($source, ...$args);
            }
        );

        $schemator->addFilter(
            'date',
            function(Schemator $schemator, ?int $source, array $rootSource, string $format, ?int $timezone = null) {
                if($source === null) {
                    return null;
                }
                if($timezone === null) {
                    return date($format, $source);
                }
                return gmdate($format, $source+3600*$timezone);
            }
        );

        $schemator->addFilter(
            'implode',
            function(Schemator $schemator, ?array $source, array $rootSource, string $delimiter = ', ') {
                if($source === null) {
                    return null;
                }
                return implode($delimiter, $source);
            }
        );

        $schemator->addFilter(
            'explode',
            function(Schemator $schemator, ?string $source, array $rootSource, string $delimiter = ', ') {
                if($source === null) {
                    return null;
                }
                return explode($delimiter, $source);
            }
        );

        $schemator->addFilter(
            'sum',
            function(Schemator $schemator, ?array $source) {
                if($source === null) {
                    return null;
                }
                return array_sum($source);
            }
        );

        $schemator->addFilter(
            'average',
            function(Schemator $schemator, ?array $source) {
                if($source === null) {
                    return null;
                }
                return array_sum($source)/count($source);
            }
        );

        $schemator->addFilter(
            'filter',
            function(Schemator $schemator, ?array $source, array $rootSource, $filterConfig) {
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
            }
        );

        $schemator->addFilter(
            'sort',
            function(Schemator $schemator, ?array $source, array $rootSource, ?callable $sortCallback = null) {
                if($source === null) {
                    return null;
                }
                if($sortCallback !== null) {
                    usort($source, $sortCallback);
                } else {
                    sort($source);
                }
                return $source;
            }
        );

        $schemator->addFilter(
            'rsort',
            function(Schemator $schemator, ?array $source, array $rootSource) {
                if($source === null) {
                    return null;
                }

                rsort($source);
                return $source;
            }
        );

        $schemator->addFilter(
            'path',
            function(Schemator $schemator, ?string $source, array $rootSource) {
                if($source === null) {
                    return null;
                }
                return $schemator->getValue($rootSource, $source);
            }
        );

        $schemator->addFilter(
            'flatten',
            function(Schemator $schemator, ?array $source) {
                if($source === null) {
                    return null;
                }
                return ArrHelper::flatten($source);
            }
        );

        $schemator->addFilter(
            'replace',
            function(Schemator $schemator, $source, array $rootSource, array $rules) {
                if($source === null) {
                    return null;
                }

                $isArray = is_array($source);

                if(!$isArray) {
                    $source = [$source];
                }

                $result = [];
                $elseValue = null;

                foreach($source as $item) {
                    $isReplaced = false;

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
            }
        );
    }
}
