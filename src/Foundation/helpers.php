<?php

use OffbeatWP\Foundation\App;

if (!function_exists('offbeat')) {
    function offbeat(): App
    {
        return App::getInstance();
    }
}

if (!function_exists('config')) {
    function config(string $config, bool $collect = true): mixed
    {
        return App::getInstance()->config($config, $collect);
    }
}

if (!function_exists('assetUrl')) {
    /** @deprecated */
    function assetUrl(string $file): string
    {
        return '';
    }
}

if (!function_exists('setting')) {
    /** @deprecated */
    function setting(string $key): mixed
    {
        return get_option('options_' . $key);
    }
}
