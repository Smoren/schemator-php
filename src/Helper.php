<?php


namespace Smoren\Schemator;


class Helper
{
    /**
     * @param array $arr
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
     * @param $value
     * @param string $rule
     * @param array $args
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
