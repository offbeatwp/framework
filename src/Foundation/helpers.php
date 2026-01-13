<?php

use OffbeatWP\Assets\AssetsManager;
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
    function assetUrl(string $file): ?string
    {
        return AssetsManager::getInstance()->getUrl($file);
    }
}
