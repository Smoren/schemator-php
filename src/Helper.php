<?php


namespace Smoren\Schemator;


/**
 * Helper class for some tools
 * @author Smoren <ofigate@gmail.com>
 */
class Helper
{
    /**
     * Flattens an array
     * @param array $arr array to flatten
     * @return array|false
     */
    public static function flattenArray(array $arr): array
    {
        if(!is_array($arr)) {
            return false;
        }

        $tmp = [];
        foreach($arr as $val) {
            if(is_array($val)) {
                $tmp = array_merge($tmp, static::flattenArray($val));
            } else {
                $tmp[] = $val;
            }
        }

        return $tmp;
    }

    /**
     * Returns true if array is associative else false
     * @param array $input array to check
     * @return bool result flag
     */
    public static function isArrayAssoc(array $input): bool
    {
        if([] === $input) return false;
        return array_keys($input) !== range(0, count($input) - 1);
    }

    /**
     * Checks rule for value
     * @param mixed $value value to check
     * @param string $rule rule for checking
     * @param array $args arguments for rule
     * @return bool
     */
    public static function checkRule($value, string $rule, array $args): bool
    {
        switch($rule) {
            case '=':
                if((string)$value === (string)$args[0]) {
                    return true;
                }
                break;
            case '!=':
                if((string)$value !== (string)$args[0]) {
                    return true;
                }
                break;
            case '>':
                if($value > $args[0]) {
                    return true;
                }
                break;
            case '>=':
                if($value >= $args[0]) {
                    return true;
                }
                break;
            case '<':
                if($value < $args[0]) {
                    return true;
                }
                break;
            case '<=':
                if($value <= $args[0]) {
                    return true;
                }
                break;
            case 'between':
                if($value >= $args[0] && $value <= $args[1]) {
                    return true;
                }
                break;
            case 'between_strict':
                if($value > $args[0] && $value < $args[1]) {
                    return true;
                }
                break;
        }

        return false;
    }
}
