<?php

/**
 * Retrieves a string value based on an option name.
 *
 * If the option does not exist or is not a string then <i>NULL</i> is returned.
 * @param non-falsy-string $config
 */
function owp_config_string(string $config): ?string
{
    return filter_var(config($config), FILTER_DEFAULT, FILTER_NULL_ON_FAILURE);
}

/**
 * Retrieves an integer value based on an option name.
 *
 * If the option does not exist or is not an integer then <i>NULL</i> is returned.
 * @param non-falsy-string $config
 */
function owp_config_int(string $config): ?int
{
    return filter_var(config($config), FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
}

/**
 * Retrieves a float value based on an option name.
 *
 * If the option does not exist or is not a float then <i>NULL</i> is returned.
 * @param non-falsy-string $config
 */
function owp_config_float(string $config): ?float
{
    return filter_var(config($config), FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
}

/**
 * Retrieves a boolean value based on an option name.
 *
 * If the option does not exist or is not a boolean then <i>NULL</i> is returned.
 * @param non-falsy-string $config
 */
function owp_config_bool(string $config): ?bool
{
    return filter_var(config($config), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
}

/**
 * Retrieves a positive integer value based on an option name.
 *
 * If the option does not exist, is not an integer or is <= 0 then <i>NULL</i> is returned.
 * @param non-falsy-string $config
 * @return positive-int|null
 */
function owp_config_positive_int(string $config): ?int
{
    $v = owp_config_int($config);
    return $v && $v > 0 ? $v : null;
}
