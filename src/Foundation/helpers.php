<?php

use OffbeatWP\Contracts\SiteSettings;
use OffbeatWP\Foundation\App;

if (!function_exists('offbeat')) {
    /**
     * @template T
     * @param class-string<T>|string|null $service
     * @return T
     */
    function offbeat(?string $service = null) {
        if ($service !== null) {
            return container($service);
        }

        return App::singleton();
    }
}

if (!function_exists('config')) {
    function config(?string $config = null, $default = null) {
        /** @var App $app */
        $app = offbeat();
        return $app->config($config, $default);
    }
}

if (!function_exists('container')) {
    /**
     * @template T
     * @param class-string<T>|string|null $definition
     * @return T
     */
    function container(?string $definition = null) {
        /** @var App $app */
        $app = offbeat();

        if ($definition !== null) {
            return $app->container->get($definition);
        }

        return $app->container;
    }
}

if (!function_exists('assetUrl')) {
    function assetUrl(string $file) {
        return offbeat('assets')->getUrl($file);
    }
}

if (!function_exists('setting')) {
    /** @param string $key */
    function setting($key) {
        return offbeat(SiteSettings::class)->get($key);
    }
}