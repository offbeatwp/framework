<?php

use DI\Container;
use OffbeatWP\Contracts\SiteSettings;
use OffbeatWP\Foundation\App;

if (!function_exists('offbeat')) {
    /**
     * @template T
     * @param class-string<T> $service
     * @return T
     */
    function offbeat(string $service = '') {
        if ($service) {
            return offbeat($service);
        }

        return App::singleton();
    }
}

if (!function_exists('container')) {
    function container(): Container
    {
        return App::singleton()->container;
    }
}

if (!function_exists('config')) {
    function config(string $config = ''): mixed
    {
        return App::singleton()->config($config);
    }
}

if (!function_exists('assetUrl')) {
    function assetUrl(string $file): ?string
    {
        return offbeat('assets')->getUrl($file);
    }
}

if (!function_exists('setting')) {
    function setting(string $key): mixed
    {
        return offbeat(SiteSettings::class)->get($key);
    }
}