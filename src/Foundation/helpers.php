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
    /**
     * @param string|null $config
     * @param null $default Unused
     * @return mixed
     */
    function config(?string $config = null, $default = null) {
        return App::singleton()->config($config, $default);
    }
}

if (!function_exists('container')) {
    /**
     * @template T
     * @param class-string<T>|string|null $definition
     * @return T
     */
    function container(?string $definition = null) {
        $app = App::singleton();

        if ($definition !== null) {
            return $app->container->get($definition);
        }

        return $app->container;
    }
}

if (!function_exists('assetUrl')) {
    /**
     * @param string $file
     * @return false|string
     */
    function assetUrl(string $file) {
        return offbeat('assets')->getUrl($file);
    }
}

if (!function_exists('setting')) {
    /**
     * @param string $key
     * @return mixed
     */
    function setting($key) {
        return offbeat(SiteSettings::class)->get($key);
    }
}