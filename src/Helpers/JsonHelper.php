<?php

namespace OffbeatWP\Helpers;

use stdClass;

final class JsonHelper
{
    public static function decodeToObject(string $string, int $depth = 512, int $flags = 0): stdClass
    {
        return json_decode($string, false, $depth, $flags) ?: new stdClass();
    }

    public static function decodeToArray(string $string, int $depth = 512, int $flags = 0): array
    {
        return json_decode($string, true, $depth, $flags) ?: [];
    }
}