<?php declare(strict_types=1);

namespace Reconmap\Utils;

class ArrayUtils
{
    public static function flatten(array|object|null $array, $prefix = '')
    {
        if (is_null($array)) {
            return [];
        }

        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = $result + self::flatten($value, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = $value;
            }
        }

        return $result;
    }
}
