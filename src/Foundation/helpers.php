<?php

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
            return container($service);
        }

        return App::singleton();
    }
}

if (!function_exists('config')) {
    function config(string $config = ''): mixed
    {
        /** @var App $app */
        $app = offbeat();
        return $app->config($config);
    }
}

if (!function_exists('container')) {
    /**
     * @template T
     * @param class-string<T> $definition
     * @return T
     */
    function container(string $definition = ''): mixed
    {
        /** @var App $app */
        $app = offbeat();

        if ($definition) {
            return $app->container->get($definition);
        }

        return $app->container;
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