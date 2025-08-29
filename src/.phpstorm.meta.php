<?php

namespace PHPSTORM_META {
    override(\offbeat(), map([
        'admin-page' => \OffbeatWP\Support\Wordpress\AdminPage::class,
        'ajax' => \OffbeatWP\Support\Wordpress\Ajax::class,
        'rest-api' => \OffbeatWP\Support\Wordpress\RestApi::class,
        'console' => \OffbeatWP\Support\Wordpress\Console::class,
        'post-type' => \OffbeatWP\Support\Wordpress\PostType::class,
        'post' => \OffbeatWP\Support\Wordpress\Post::class,
        'page' => \OffbeatWP\Support\Wordpress\Page::class,
        'taxonomy' => \OffbeatWP\Support\Wordpress\Taxonomy::class,
        'design' => \OffbeatWP\Support\Wordpress\Design::class,
        'enqueue-script' => \OffbeatWP\Content\Enqueue\WpScriptEnqueueBuilder::class,
        'enqueue-style' => \OffbeatWP\Content\Enqueue\WpStyleEnqueueBuilder::class,
        'assets' => \OffbeatWP\Assets\AssetsManager::class,
        'http' => \OffbeatWP\Http\Http::class
    ]));

    override(\container(), map([
        'admin-page' => \OffbeatWP\Support\Wordpress\AdminPage::class,
        'ajax' => \OffbeatWP\Support\Wordpress\Ajax::class,
        'rest-api' => \OffbeatWP\Support\Wordpress\RestApi::class,
        'console' => \OffbeatWP\Support\Wordpress\Console::class,
        'post-type' => \OffbeatWP\Support\Wordpress\PostType::class,
        'post' => \OffbeatWP\Support\Wordpress\Post::class,
        'page' => \OffbeatWP\Support\Wordpress\Page::class,
        'taxonomy' => \OffbeatWP\Support\Wordpress\Taxonomy::class,
        'design' => \OffbeatWP\Support\Wordpress\Design::class,
        'enqueue-script' => \OffbeatWP\Content\Enqueue\WpScriptEnqueueBuilder::class,
        'enqueue-style' => \OffbeatWP\Content\Enqueue\WpStyleEnqueueBuilder::class,
        'assets' => \OffbeatWP\Assets\AssetsManager::class,
        'http' => \OffbeatWP\Http\Http::class
    ]));
}