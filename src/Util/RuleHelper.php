<?php

namespace Smoren\Schemator\Util;

/**
 * @internal
 */
class RuleHelper
{
    /**
     * Checks rule for value
     * @param mixed $value value to check
     * @param string $rule rule for checking
     * @param array $args arguments for rule
     * @return bool
     */
    public static function evaluate($value, string $rule, array $args): bool
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
            case 'in':
                if(in_array($value, $args[0])) {
                    return true;
                }
                break;
            case 'not in':
                if(!in_array($value, $args[0])) {
                    return true;
                }
                break;
            case 'between strict':
                if($value > $args[0] && $value < $args[1]) {
                    return true;
                }
                break;
        }

        return false;
    }
}
