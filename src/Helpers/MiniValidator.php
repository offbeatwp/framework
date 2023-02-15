<?php

namespace OffbeatWP\Helpers;

final class MiniValidator
{
    /**
     * Check if the given value is a valid phone number.<br>
     * Note: This only does some sanity checks.
     */
    public function isPhone($value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        if (preg_match_all( '/\d/', $value) < 3) {
            return null;
        }

        if (strlen($value) > 50) {
            return null;
        }

        return htmlentities($value);
    }

    public static function isEmail($value): ?string
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
    }
}