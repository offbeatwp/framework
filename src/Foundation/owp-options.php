<?php

function owp_get_option_string(string $option): ?string
{
    return filter_var(get_option($option, null), FILTER_DEFAULT,  FILTER_NULL_ON_FAILURE);
}

function owp_get_option_int(string $option): ?int
{
    return filter_var(get_option($option, null), FILTER_VALIDATE_INT,  FILTER_NULL_ON_FAILURE);
}

function owp_get_option_float(string $option): ?float
{
    return filter_var(get_option($option, null), FILTER_VALIDATE_FLOAT,  FILTER_NULL_ON_FAILURE);
}

function owp_get_option_bool(string $option): ?bool
{
    return filter_var(get_option($option, null), FILTER_VALIDATE_BOOL,  FILTER_NULL_ON_FAILURE);
}
