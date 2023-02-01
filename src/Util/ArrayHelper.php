<?php

namespace Smoren\Schemator\Util;

/**
 * @internal
 */
class ArrayHelper
{
    /**
     * Flattens an array
     * @param array $arr array to flatten
     * @return array flat array
     */
    public static function flatten(array $arr): array
    {
        $tmp = [];
        foreach($arr as $val) {
            if(is_array($val)) {
                $tmp = array_merge($tmp, static::flatten($val));
            } else {
                $tmp[] = $val;
            }
        }

        return $tmp;
    }
}
