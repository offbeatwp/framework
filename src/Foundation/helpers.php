<?php

use OffbeatWP\Contracts\SiteSettings;
use OffbeatWP\Foundation\App;

if (!function_exists('offbeat')) {
    /** @return mixed */
    function offbeat($service = null) {
        if (!is_null($service)) {
            return container($service);
        }

        return App::singleton();
    }
}

if (!function_exists('config')) {
    function config($config = null, $default = null) {
        return offbeat()->config($config, $default);
    }
}

if (!function_exists('container')) {
    function container($definition = null)
    {
        if (!is_null($definition))
            return offbeat()->container->get($definition);

        return offbeat()->container;
    }
}

if (!function_exists('assetUrl')) {
    function assetUrl($file) {
        return offbeat('assets')->getUrl($file);
    }
}

if (!function_exists('setting')) {
    function setting($key) {
        return offbeat(SiteSettings::class)->get($key);
    }
}