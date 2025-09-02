<?php

use OffbeatWP\Foundation\App;

if (!function_exists('offbeat')) {
    function offbeat(): App
    {
        return App::getInstance();
    }
}

if (!function_exists('config')) {
    function config(?string $config = null, bool $collect = true): mixed
    {
        return App::getInstance()->config($config, $collect);
    }
}

if (!function_exists('container')) {
    /**
     * @template T
     * @param class-string<T>|string $definition
     * @return T
     */
    function container(string $definition)
    {
        return App::getInstance()->container->get($definition);
    }
}

if (!function_exists('assetUrl')) {
    function assetUrl(string $file): ?string
    {
        return null;
    }
}

if (!function_exists('setting')) {
    function setting(string $key): mixed
    {
        return get_option('options_' . $key);
    }
}
