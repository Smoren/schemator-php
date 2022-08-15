<?php

namespace Smoren\Schemator\Filters;

use ArrayIterator;
use Smoren\Helpers\ArrHelper;
use Smoren\Helpers\RuleHelper;
use Smoren\Schemator\Interfaces\FilterContextInterface;
use Smoren\Schemator\Interfaces\FiltersStorageInterface;

class BaseFiltersStorage implements FiltersStorageInterface
{
    public static function const(
        FilterContextInterface $context,
        $constValue
    ) {
        return $constValue;
    }

    public static function format(
        FilterContextInterface $context,
        callable $formatter,
        ...$args
    ) {
        return $formatter($context->getSource(), ...$args);
    }

    public static function date(
        FilterContextInterface $context,
        string $format,
        ?int $timezone = null
    ) {
        $source = $context->getSource();
        if($source === null) {
            return null;
        }
        if($timezone === null) {
            return date($format, $source);
        }
        return gmdate($format, $source+3600*$timezone);
    }

    public static function implode(
        FilterContextInterface $context,
        string $delimiter = ', '
    ): ?string {
        $source = $context->getSource();
        if($source === null) {
            return null;
        }
        return implode($delimiter, $source);
    }

    public static function explode(
        FilterContextInterface $context,
        string $delimiter = ', '
    ) {
        $source = $context->getSource();
        if($source === null) {
            return null;
        }
        return explode($delimiter, $source);
    }

    public static function sum(FilterContextInterface $context)
    {
        $source = $context->getSource();
        if($source === null) {
            return null;
        }
        return array_sum($source);
    }

    public static function average(FilterContextInterface $context)
    {
        $source = $context->getSource();
        if($source === null) {
            return null;
        }
        return array_sum($source)/count($source);
    }

    public static function filter(
        FilterContextInterface $context,
        $filterConfig
    ): ?array
    {
        $source = $context->getSource();
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

    public static function sort(
        FilterContextInterface $context,
        ?callable $sortCallback = null
    ): ?array {
        $source = $context->getSource();
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

    public static function rsort(FilterContextInterface $context): ?array
    {
        $source = $context->getSource();
        if($source === null) {
            return null;
        }

        rsort($source);
        return $source;
    }

    public static function path(FilterContextInterface $context)
    {
        $source = $context->getSource();
        if($source === null) {
            return null;
        }
        return $context->getSchemator()->getValue($context->getRootSource(), $source);
    }

    public static function flatten(FilterContextInterface $context): ?array
    {
        $source = $context->getSource();
        if($source === null) {
            return null;
        }
        return ArrHelper::flatten($source);
    }

    public static function replace(FilterContextInterface $context, array $rules)
    {
        $source = $context->getSource();
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
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->_get());
    }

    protected function _get(): array
    {
        return [
            'const' => [$this, 'const'],
            'format' => [$this, 'format'],
            'date' => [$this, 'date'],
            'implode' => [$this, 'implode'],
            'explode' => [$this, 'explode'],
            'sum' => [$this, 'sum'],
            'average' => [$this, 'average'],
            'filter' => [$this, 'filter'],
            'sort' => [$this, 'sort'],
            'rsort' => [$this, 'rsort'],
            'path' => [$this, 'path'],
            'flatten' => [$this, 'flatten'],
            'replace' => [$this, 'replace'],
        ];
    }
}
