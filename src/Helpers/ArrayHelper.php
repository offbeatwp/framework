<?php
namespace OffbeatWP\Helpers;

use InvalidArgumentException;

class ArrayHelper {
    /**
     * @param mixed $input
     * @return bool
     */
    public static function isAssoc($input): bool
    {
        return is_array($input) && $input && array_keys($input) !== range(0, count($input) - 1);
    }

    /**
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
     * @param string $key
     * @param mixed[] $array
     * @return mixed
     */
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
     * @param int $minAmount
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

    /**
     * Recursively removes all NULL values from an array<br>
     * This passed array may not contain any objects
     * @throws InvalidArgumentException
     * @param scalar[]|null[]|mixed[][] $array
     * @return scalar[]|mixed[][]
     */
    public static function filterNull(array $array): array
    {
        foreach ($array as $key => $value) {
            if ($value === null) {
                unset($array[$key]);
            } elseif (is_array($value)) {
                $array[$key] = ArrayHelper::filterNull($value);
            } elseif (!is_scalar($value)) {
                throw new InvalidArgumentException('Array key ' . $key . ' has illegal value type: ' . gettype($value));
            }
        }

        return $array;
    }
}