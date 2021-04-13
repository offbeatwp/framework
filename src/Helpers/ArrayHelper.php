<?php
namespace OffbeatWP\Helpers;

class ArrayHelper {
    public static function isAssoc(array $array): bool
    {
        return is_array($array) && array_keys($array) !== range(0, count($array) - 1);
    }

    public static function mergeRecursiveAssoc(array $array1, array $array2): array
    {
        $array = [];

        foreach ($array1 as $key => $value) {
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

        return $array;
    }

    public static function getValueFromDottedKey(string $key, array $array = [])
    {
        if (!is_array($array)) {
            return false;
        }

        foreach (explode('.', $key) as $var) {
            if (isset($array[$var])) {
                $array = $array[$var];
            } else {
                return false;
            }
        }

        return $array;
    }
}