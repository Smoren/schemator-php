<?php

namespace Smoren\Schemator\Filters;

use ArrayIterator;
use Smoren\Schemator\Util\ArrayHelper;
use Smoren\Schemator\Util\RuleHelper;
use Smoren\Schemator\Exceptions\SchematorException;
use Smoren\Schemator\Interfaces\FilterContextInterface;
use Smoren\Schemator\Interfaces\FiltersStorageInterface;

/**
 * Class BaseFiltersStorage
 * @author Smoren <ofigate@gmail.com>
 */
class BaseFiltersStorage implements FiltersStorageInterface
{
    /**
     * Returns const value
     * @param FilterContextInterface $context filter context
     * @param mixed $constValue const value to return
     * @return mixed
     */
    public static function const(FilterContextInterface $context, $constValue)
    {
        return $constValue;
    }

    /**
     * Formats source value with formatter callback
     * @param FilterContextInterface $context filter context
     * @param callable $formatter formatter callback
     * @param mixed ...$args formatter callback's arguments
     * @return mixed
     */
    public static function format(FilterContextInterface $context, callable $formatter, ...$args)
    {
        return $formatter($context->getSource(), ...$args);
    }

    /**
     * Returns formatted date from timestamp
     * @param FilterContextInterface $context filter context
     * @param string $format php date format
     * @param int|null $timezone timezone offset
     * @return false|string|null
     * @throws SchematorException
     */
    public static function date(FilterContextInterface $context, string $format, ?int $timezone = null)
    {
        $source = $context->getSource();
        if ($source === null) {
            throw SchematorException::createAsBadFilterSource($context);
        }
        if ($timezone === null) {
            return date($format, intval($source));
        }
        return gmdate($format, $source + 3600 * $timezone);
    }

    /**
     * Implodes array with separator
     * @param FilterContextInterface $context filter context
     * @param string $delimiter separator
     * @return string|null
     * @throws SchematorException
     */
    public static function implode(FilterContextInterface $context, string $delimiter = ', '): ?string
    {
        $source = $context->getSource();
        if ($source === null || !is_array($source)) {
            throw SchematorException::createAsBadFilterSource($context);
        }
        return implode($delimiter, $source);
    }

    /**
     * Explodes array with separator
     * @param FilterContextInterface $context filter context
     * @param non-empty-string $delimiter separator
     * @return false|string[]|null
     * @throws SchematorException
     */
    public static function explode(FilterContextInterface $context, string $delimiter = ', ')
    {
        $source = $context->getSource();
        if ($source === null || !is_scalar($source)) {
            throw SchematorException::createAsBadFilterSource($context);
        }
        return explode($delimiter, (string)$source);
    }

    /**
     * Returns the sum of array items
     * @param FilterContextInterface $context filter context
     * @return float|int|null
     * @throws SchematorException
     */
    public static function sum(FilterContextInterface $context)
    {
        $source = $context->getSource();
        if ($source === null || !is_array($source)) {
            throw SchematorException::createAsBadFilterSource($context);
        }
        return array_sum($source);
    }

    /**
     * Returns the average value of array items
     * @param FilterContextInterface $context filter context
     * @return float|int|null
     * @throws SchematorException
     */
    public static function average(FilterContextInterface $context)
    {
        $source = $context->getSource();
        if ($source === null || !is_array($source)) {
            throw SchematorException::createAsBadFilterSource($context);
        }
        return array_sum($source) / count($source);
    }

    /**
     * Applies smart filter with rules
     * @param FilterContextInterface $context filter context
     * @param array<int, mixed>|callable $filterConfig filter rules config or filter callback
     * @return array<int, mixed>|null
     * @throws SchematorException
     */
    public static function filter(FilterContextInterface $context, $filterConfig): ?array
    {
        $source = $context->getSource();
        if ($source === null || !is_array($source)) {
            throw SchematorException::createAsBadFilterSource($context);
        }

        if (is_callable($filterConfig)) {
            return array_values(array_filter($source, $filterConfig));
        }

        if (!is_array($filterConfig)) {
            throw SchematorException::createAsBadFilterConfig($context);
        }

        $result = [];

        foreach ($source as $item) {
            foreach ($filterConfig as $args) {
                if (!is_array($args)) {
                    throw SchematorException::createAsBadFilterConfig($context);
                }

                $rule = array_shift($args);

                if (RuleHelper::evaluate($item, $rule, $args)) {
                    $result[] = $item;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Returns sorted array
     * @param FilterContextInterface $context filter context
     * @param callable|null $sortCallback sort callback
     * @return array<int, mixed>|null
     * @throws SchematorException
     */
    public static function sort(FilterContextInterface $context, ?callable $sortCallback = null): ?array
    {
        $source = $context->getSource();
        if ($source === null || !is_array($source)) {
            throw SchematorException::createAsBadFilterSource($context);
        }
        if ($sortCallback !== null) {
            usort($source, $sortCallback);
        } else {
            sort($source);
        }
        return $source;
    }

    /**
     * Returns reverse-sorted array
     * @param FilterContextInterface $context filter context
     * @return array<int, mixed>|null
     * @throws SchematorException
     */
    public static function rsort(FilterContextInterface $context): ?array
    {
        $source = $context->getSource();
        if ($source === null || !is_array($source)) {
            throw SchematorException::createAsBadFilterSource($context);
        }

        rsort($source);
        return $source;
    }

    /**
     * Returns value from root source by dynamic path got with path from source data
     * @param FilterContextInterface $context filter context
     * @return mixed|null
     * @throws SchematorException
     */
    public static function path(FilterContextInterface $context)
    {
        $source = $context->getSource();
        if ($source === null) {
            throw SchematorException::createAsBadFilterSource($context);
        }
        return $context->getSchemator()->getValue($context->getRootSource(), $source);
    }

    /**
     * Returns flattened array
     * @param FilterContextInterface $context filter context
     * @return array<int, mixed>|null
     * @throws SchematorException
     */
    public static function flatten(FilterContextInterface $context): ?array
    {
        $source = $context->getSource();
        if ($source === null || !is_array($source)) {
            throw SchematorException::createAsBadFilterSource($context);
        }
        return ArrayHelper::flatten($source);
    }

    /**
     * Applies smart filter replacements to source
     * @param FilterContextInterface $context filter context
     * @param array<int, mixed> $rules smart filter replacements
     * @return array<int, mixed>|mixed|null
     * @throws SchematorException
     */
    public static function replace(FilterContextInterface $context, array $rules)
    {
        $source = $context->getSource();
        if ($source === null) {
            throw SchematorException::createAsBadFilterSource($context);
        }

        $isArray = is_array($source);

        if (!$isArray) {
            $source = [$source];
        }

        $result = [];

        foreach ($source as $item) {
            $isReplaced = false;
            $elseValue = $item;

            foreach ($rules as $args) {
                if (!is_array($args)) {
                    throw SchematorException::createAsBadFilterConfig($context);
                }

                $value = array_shift($args);
                $rule = array_shift($args);

                if ($rule === 'else') {
                    $elseValue = $value;
                }

                $replace = null;

                if (RuleHelper::evaluate($item, $rule, $args)) {
                    $replace = $value;
                    $isReplaced = true;

                    $result[] = $replace;
                    break;
                }
            }

            if (!$isReplaced) {
                $result[] = $elseValue;
            }
        }

        if (!$isArray) {
            $result = $result[0];
        }

        return $result;
    }

    /**
     * BaseFiltersStorage constructor.
     */
    public function __construct()
    {
    }

    /**
     * @inheritDoc
     * @return ArrayIterator<string, callable>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->_get());
    }

    /**
     * Returns filters callable map
     * @return array<string, callable>
     */
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
