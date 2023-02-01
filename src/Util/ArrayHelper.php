<?php

namespace Smoren\Schemator\Util;

/**
 * @internal
 */
class ArrayHelper
{
    /**
     * Flattens an array.
     *
     * @param array<mixed> $input array to flatten
     *
     * @return array<scalar|object> flat array
     */
    public static function flatten(array $input): array
    {
        $tmp = [];
        foreach($input as $val) {
            if(is_array($val)) {
                $tmp = array_merge($tmp, static::flatten($val));
            } else {
                $tmp[] = $val;
            }
        }

        return $tmp;
    }
}
