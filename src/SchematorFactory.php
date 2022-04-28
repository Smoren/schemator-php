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
            'format',
            function(Schemator $executor, $source, array $rootSource, callable $formatter, ...$args) {
                return $formatter($source, ...$args);
            }
        );

        $schemator->addFilter(
            'date',
            function(Schemator $executor, int $source, array $rootSource, string $format, ?int $timezone = null) {
                if($timezone === null) {
                    return date($format, $source);
                }
                return gmdate($format, $source+3600*$timezone);
            }
        );

        $schemator->addFilter(
            'implode',
            function(Schemator $executor, array $source, array $rootSource, string $delimiter = ', ') {
                return implode($delimiter, $source);
            }
        );

        $schemator->addFilter(
            'explode',
            function(Schemator $executor, string $source, array $rootSource, string $delimiter = ', ') {
                return explode($delimiter, $source);
            }
        );

        $schemator->addFilter(
            'filter',
            function(Schemator $executor, array $source, array $rootSource, $filterConfig) {
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
            function(Schemator $executor, array $source, array $rootSource, ?callable $sortCallback = null) {
                if($sortCallback !== null) {
                    usort($source, $sortCallback);
                } else {
                    sort($source);
                }
                return $source;
            }
        );

        $schemator->addFilter(
            'path',
            function(Schemator $executor, string $source, array $rootSource) {
                return $executor->getValue($rootSource, $source);
            }
        );

        $schemator->addFilter(
            'flatten',
            function(Schemator $executor, array $source) {
                return ArrHelper::flatten($source);
            }
        );

        $schemator->addFilter(
            'replace',
            function(Schemator $executor, array $source, array $rootSource, array $rules) {
                $result = [];

                foreach($source as $item) {
                    $isReplaced = false;

                    foreach($rules as $args) {
                        $value = array_shift($args);
                        $rule = array_shift($args);

                        $replace = null;

                        if(RuleHelper::check($item, $rule, $args)) {
                            $replace = $value;
                            $isReplaced = true;

                            $result[] = $replace;
                            break;
                        }
                    }

                    if(!$isReplaced) {
                        $result[] = null;
                    }
                }

                return $result;
            }
        );
    }
}
