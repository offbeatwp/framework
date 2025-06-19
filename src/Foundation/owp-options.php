<?php

/**
 * Retrieves an option string value based on an option name.
 *
 * If the option does not exist or is not a string, <i>NULL</i> is returned.
 * @param non-falsy-string $option
 */
function owp_get_option_string(string $option): ?string
{
    return filter_var(get_option($option, null), FILTER_DEFAULT,  FILTER_NULL_ON_FAILURE);
}

/**
 * Retrieves an option integer value based on an option name.
 *
 * If the option does not exist or is not an integer, <i>NULL</i> is returned.
 * @param non-falsy-string $option
 */
function owp_get_option_int(string $option): ?int
{
    return filter_var(get_option($option, null), FILTER_VALIDATE_INT,  FILTER_NULL_ON_FAILURE);
}

/**
 * Retrieves an option float value based on an option name.
 *
 * If the option does not exist or is not a float, <i>NULL</i> is returned.
 * @param non-falsy-string $option
 */
function owp_get_option_float(string $option): ?float
{
    return filter_var(get_option($option, null), FILTER_VALIDATE_FLOAT,  FILTER_NULL_ON_FAILURE);
}

/**
 * Retrieves an option boolean value based on an option name.
 *
 * If the option does not exist or is not a boolean, <i>NULL</i> is returned.
 * @param non-falsy-string $option
 */
function owp_get_option_bool(string $option): ?bool
{
    return filter_var(get_option($option, null), FILTER_VALIDATE_BOOL,  FILTER_NULL_ON_FAILURE);
}
