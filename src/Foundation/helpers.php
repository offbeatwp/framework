<?php

use OffbeatWP\Content\Post\PostTypeBuilder;
use OffbeatWP\Content\Taxonomy\TaxonomyBuilder;
use OffbeatWP\Contracts\SiteSettings;
use OffbeatWP\Foundation\App;
use OffbeatWP\Support\Wordpress\AdminPage;
use OffbeatWP\Support\Wordpress\Ajax;
use OffbeatWP\Support\Wordpress\Console;
use OffbeatWP\Support\Wordpress\Design;
use OffbeatWP\Support\Wordpress\Hooks;
use OffbeatWP\Support\Wordpress\Page;
use OffbeatWP\Support\Wordpress\Post;
use OffbeatWP\Support\Wordpress\PostType;
use OffbeatWP\Support\Wordpress\RestApi;
use OffbeatWP\Support\Wordpress\Taxonomy;

if (!function_exists('offbeat')) {
    /**
     * @param string|null $service
     * @return App|Taxonomy|AdminPage|Ajax|RestApi|Console|Hooks|PostType|Post|Page|Design|PostTypeBuilder|TaxonomyBuilder
     */
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
    function container($definition = null) {
        if (!is_null($definition)) {
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