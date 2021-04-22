<?php
namespace OffbeatWP\Helpers;

class ArrayHelper {
    public static function isAssoc($array): bool
    {
        return is_array($array) && !empty($array) && array_keys($array) !== range(0, count($array) - 1);
    }

    public static function mergeRecursiveAssoc(array $array1, array $array2): array
    {
        $array = [];

        if(!empty($array1)) foreach ($array1 as $key => $value) {
            if (!isset($array2[$key])) {
                $array[$key] = $array1[$key];
                continue;
            }

            if (is_array($array1[$key]) && self::isAssoc($array1[$key])) {
                $array[$key] = self::mergeRecursiveAssoc($array1[$key], $array2[$key]);
            } else {
                $array[$key] = $array2[$key];
            }
        }

        if(!empty($array2)) foreach ($array2 as $key => $value) {
            if (!isset($array1[$key])) {
                $array[$key] = $array2[$key];
                continue;
            }
        }

        return $array;
    }

    public static function getValueFromDottedKey(string $key, array $array = [])
    {
        if (!is_array($array)) {
            return null;
        }

        foreach (explode('.', $key) as $var) {
            if (isset($array[$var])) {
                $array = $array[$var];
            } else {
                return null;
            }
        }

        return $array;
    }
}