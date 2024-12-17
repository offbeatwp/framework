<?php

use OffbeatWP\Contracts\SiteSettings;
use OffbeatWP\Foundation\App;

if (!function_exists('offbeat')) {
    /**
     * @template T
     * @param class-string<T>|string|null $service
     * @return T
     */
    function offbeat(?string $service = null)
    {
        if ($service !== null) {
            return container($service);
        }

        return App::singleton();
    }
}

if (!function_exists('config')) {
    /**
     * @param string|null $config Config slug. Dotted keys annotation can be used.
     * @param int|null $filter Filter to use.<br>
     * If <i>NULL</i> is passed, arrays will be converted to collections to match legacy behavior of this function.<br>
     * The FILTER_NULL_ON_FAILURE option is enabled by default which means <i>NULL</i> is returned if the filter fails.
     * @see filter_var
     */
    function config(?string $config = null, ?int $filter = null): mixed
    {
        if ($config === null) {
            trigger_error('Passing NULL as first parameter to config is deprecated.', E_USER_DEPRECATED);
        }

        return App::singleton()->config($config, $filter);
    }
}

if (!function_exists('container')) {
    /**
     * @template T
     * @param class-string<T>|string|null $definition
     * @return T
     */
    function container(?string $definition = null)
    {
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
    function assetUrl(string $file)
    {
        return offbeat('assets')->getUrl($file);
    }
}

if (!function_exists('setting')) {
    /**
     * @param string $key
     * @return mixed
     */
    function setting($key)
    {
        return offbeat(SiteSettings::class)->get($key);
    }
}
