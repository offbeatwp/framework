<?php

use OffbeatWP\Contracts\SiteSettings;
use OffbeatWP\Foundation\App;

if (!function_exists('offbeat')) {
    function offbeat($service = null) {
        if ($service !== null) {
            return container($service);
        }

        return App::singleton();
    }
}

if (!function_exists('config')) {
    function config(?string $config = null, $default = null) {
        return offbeat()->config($config, $default);
    }
}

if (!function_exists('container')) {
    function container($definition = null) {
        if ($definition !== null) {
            return offbeat()->container->get($definition);
        }

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