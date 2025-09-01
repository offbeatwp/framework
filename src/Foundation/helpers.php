<?php

use OffbeatWP\Foundation\App;

if (!function_exists('offbeat')) {
    function offbeat(): App
    {
        return App::singleton();
    }
}

if (!function_exists('config')) {
    function config(?string $config = null, bool $collect = true): mixed
    {
        return App::singleton()->config($config, $collect);
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
        return App::singleton()->container->get($definition);
    }
}

if (!function_exists('assetUrl')) {
    function assetUrl(string $file): ?string
    {
        return container('assets')->getUrl($file);
    }
}

if (!function_exists('setting')) {
    function setting(string $key): mixed
    {
        return get_option('options_' . $key);
    }
}
