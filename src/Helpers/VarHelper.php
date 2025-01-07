<?php

namespace OffbeatWP\Helpers;

final class VarHelper
{
    private function __construct()
    {
        // This class is not instantiable
    }

    /**
     * If the given value passed the FILTER_UNSAFE_RAW filter, it will be cast to a <i>string</i> and returned.<br>
     * Otherwise, the value of <b>$default</b> is returned.
     */
    public static function toString(mixed $value, ?string $default = null): ?string
    {
        $v = filter_var($value, FILTER_UNSAFE_RAW);
        return is_string($v) ? $v : $default;
    }

    /**
     * If the given value passed the FILTER_VALIDATE_INT filter, it will be cast to a <i>int</i> and returned.<br>
     * Otherwise, the value of <b>$default</b> is returned.
     */
    public static function toInt(mixed $value, ?int $default = null): ?int
    {
        $v = filter_var($value, FILTER_VALIDATE_INT);
        return is_int($v) ? $v : $default;
    }

    /**
     * If the given value passed the FILTER_VALIDATE_FLOAT filter, it will be cast to a <i>float</i> and returned.<br>
     * Otherwise, the value of <b>$default</b> is returned.
     */
    public static function toFloat(mixed $value, ?float $default = null): ?float
    {
        $v = filter_var($value, FILTER_VALIDATE_FLOAT);
        return is_float($v) ? $v : $default;
    }

    /**
     * If the given value passed the FILTER_VALIDATE_BOOLEAN filter, it will be cast to a <i>bool</i> and returned.<br>
     * Otherwise, the value of <b>$default</b> is returned.
     */
    public static function toBool(mixed $value, ?bool $default = null): ?bool
    {
        $v = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return is_bool($v) ? $v : $default;
    }

    /**
     * If the given value passed the FILTER_REQUIRE_ARRAY filter, it will be cast to an <i>array</i> and returned.<br>
     * Otherwise, the value of <b>$default</b> is returned.
     */
    public static function toArray(mixed $value, ?array $default = null): ?array
    {
        $v = filter_var($value, FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY);
        return is_array($v) ? $v : $default;
    }
}
