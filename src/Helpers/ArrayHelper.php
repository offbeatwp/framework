<?php
namespace OffbeatWP\Helpers;

class ArrayHelper {
    public static function isAssoc($array): bool
    {
        return is_array($array) && $array && array_keys($array) !== range(0, count($array) - 1);
    }

    public static function mergeRecursiveAssoc(iterable $array1, iterable $array2): array
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

        foreach ($array2 as $key => $value) {
            if (!isset($array1[$key])) {
                $array[$key] = $array2[$key];
            }
        }

        return $array;
    }

    public static function getValueFromDottedKey(string $key, iterable $array = [])
    {
        foreach (explode('.', $key) as $var) {
            if (isset($array[$var])) {
                $array = $array[$var];
            } else {
                return null;
            }
        }

        return $array;
    }

    /**
     * @param scalar|scalar[] $data
     * @param bool $deleteNonNumericValues When true, any non-numeric values found in the array are deleted.
     * @return int[]
     */
    public static function toIntArray($data, bool $deleteNonNumericValues = false): array
    {
        $data = (array)$data;

        if ($deleteNonNumericValues) {
            $data = array_filter($data, 'is_numeric');
        }

        return array_map('intval', $data);
    }

    /**
     * Retrieve a single random value from an array.
     * @template T
     * @param T[] $array
     * @return T|null
     */
    public static function randomValue(array $array)
    {
        $randKey = array_rand($array);
        return $array[$randKey];
    }

    /**
     * Retrieve several random values from an array.
     * @template T
     * @param T[] $array
     * @param positive-int $minAmount
     * @param int $maxAmount
     * @return T[]
     */
    public static function randomValues(array $array, int $minAmount, int $maxAmount = 0): array
    {
        $amount = ($maxAmount > $minAmount) ? mt_rand($minAmount, $maxAmount) : $minAmount;

        $output = [];
        $randKeys = array_rand($array, $amount);

        foreach ($randKeys as $randKey) {
            $output[] = $array[$randKey];
        }

        return $output;
    }
}