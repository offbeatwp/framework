<?php

namespace OffbeatWP\Helpers;

final class ArrayHelper
{
    /**
     * Consider using <b>array_is_list</b> instead
     * @pure
     * @see \array_is_list()
     */
    public static function isAssoc(mixed $input): bool
    {
        return is_array($input) && $input && array_keys($input) !== range(0, count($input) - 1);
    }

    /**
     * @pure
     * @param mixed[] $array1
     * @param mixed[] $array2
     * @return mixed[]
     */
    public static function mergeRecursiveAssoc(iterable $array1, iterable $array2): array
    {
        $array = [];

        foreach ($array1 as $key1 => $value1) {
            if (!isset($array2[$key1])) {
                $array[$key1] = $value1;
                continue;
            }

            if (is_array($value1) && self::isAssoc($value1)) {
                $array[$key1] = self::mergeRecursiveAssoc($value1, $array2[$key1]);
            } else {
                $array[$key1] = $array2[$key1];
            }
        }

        foreach ($array2 as $key2 => $value2) {
            if (!isset($array1[$key2])) {
                $array[$key2] = $value2;
            }
        }

        return $array;
    }

    /**
     * @pure
     * @param mixed[] $array
     */
    public static function getValueFromDottedKey(string $key, iterable $array = []): mixed
    {
        return self::getValueFromStringArray(explode('.', $key), $array);
    }

    /**
     * @pure
     * @interal
     * @param string[] $keys
     * @param iterable<mixed> $array
     */
    public static function getValueFromStringArray(array $keys, iterable $array = []): mixed
    {
        foreach ($keys as $var) {
            if (isset($array[$var])) {
                $array = $array[$var];
            } else {
                return null;
            }
        }

        return $array;
    }
}
