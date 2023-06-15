<?php

declare(strict_types=1);

namespace Smoren\Schemator\Helpers;

/**
 * @internal
 */
class RuleHelper
{
    /**
     * Checks rule for value.
     *
     * @param mixed $value value to check
     * @param string $rule rule for checking
     * @param array<mixed> $args arguments for rule
     *
     * @return bool
     */
    public static function evaluate($value, string $rule, array $args): bool
    {
        switch ($rule) {
            case '=':
                /**
                 * @var scalar $lhs
                 * @var scalar $rhs
                 */
                [$lhs, $rhs] = [$value, $args[0]];
                if (strval($lhs) === strval($rhs)) {
                    return true;
                }
                break;
            case '!=':
                /**
                 * @var scalar $lhs
                 * @var scalar $rhs
                 */
                [$lhs, $rhs] = [$value, $args[0]];
                if (strval($lhs) !== strval($rhs)) {
                    return true;
                }
                break;
            case '>':
                if ($value > $args[0]) {
                    return true;
                }
                break;
            case '>=':
                if ($value >= $args[0]) {
                    return true;
                }
                break;
            case '<':
                if ($value < $args[0]) {
                    return true;
                }
                break;
            case '<=':
                if ($value <= $args[0]) {
                    return true;
                }
                break;
            case 'between':
                if ($value >= $args[0] && $value <= $args[1]) {
                    return true;
                }
                break;
            case 'between strict':
                if ($value > $args[0] && $value < $args[1]) {
                    return true;
                }
                break;
            case 'in':
                /** @var array{array<mixed>} $args */
                if (in_array($value, $args[0])) {
                    return true;
                }
                break;
            case 'not in':
                /** @var array{array<mixed>} $args */
                if (!in_array($value, $args[0])) {
                    return true;
                }
                break;
        }

        return false;
    }
}
