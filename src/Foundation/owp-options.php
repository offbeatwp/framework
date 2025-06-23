<?php

/**
 * Retrieves a string value based on an option name.
 *
 * If the option does not exist or is not a string then <i>NULL</i> is returned.
 * @param non-falsy-string $option
 */
function owp_get_option_string(string $option): ?string
{
    return filter_var(get_option($option, null), FILTER_DEFAULT,  FILTER_NULL_ON_FAILURE);
}

/**
 * Retrieves an integer value based on an option name.
 *
 * If the option does not exist or is not an integer then <i>NULL</i> is returned.
 * @param non-falsy-string $option
 */
function owp_get_option_int(string $option): ?int
{
    return filter_var(get_option($option, null), FILTER_VALIDATE_INT,  FILTER_NULL_ON_FAILURE);
}

/**
 * Retrieves a float value based on an option name.
 *
 * If the option does not exist or is not a float then <i>NULL</i> is returned.
 * @param non-falsy-string $option
 */
function owp_get_option_float(string $option): ?float
{
    return filter_var(get_option($option, null), FILTER_VALIDATE_FLOAT,  FILTER_NULL_ON_FAILURE);
}

/**
 * Retrieves a boolean value based on an option name.
 *
 * If the option does not exist or is not a boolean then <i>NULL</i> is returned.
 * @param non-falsy-string $option
 */
function owp_get_option_bool(string $option): ?bool
{
    return filter_var(get_option($option, null), FILTER_VALIDATE_BOOL,  FILTER_NULL_ON_FAILURE);
}

/**
 * Retrieves a positive integer value based on an option name.
 *
 * If the option does not exist, is not an integer or is <= 0 then <i>NULL</i> is returned.
 * @param non-falsy-string $option
 * @return positive-int|null
 */
function owp_get_option_positive_int(string $option): ?int
{
    $v = owp_get_option_int($option);
    return $v && $v > 0 ? $v : null;
}