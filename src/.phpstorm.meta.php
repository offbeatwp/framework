<?php

namespace PHPSTORM_META {
    override(\offbeat(), map([
        'admin-page' => OffbeatWP\Support\Wordpress\AdminPage::class,
        'ajax' => OffbeatWP\Support\Wordpress\Ajax::class,
        'rest-api' => OffbeatWP\Support\Wordpress\RestApi::class,
        'console' => OffbeatWP\Support\Wordpress\Console::class,
        'hooks' => OffbeatWP\Support\Wordpress\Hooks::class,
        'post-type' => OffbeatWP\Support\Wordpress\PostType::class,
        'post' => OffbeatWP\Support\Wordpress\Post::class,
        'page' => OffbeatWP\Support\Wordpress\Page::class,
        'taxonomy' => OffbeatWP\Support\Wordpress\Taxonomy::class,
        'design' => OffbeatWP\Support\Wordpress\Design::class,
        'components' => OffbeatWP\Components\ComponentRepository::class,
        'routes' => OffbeatWP\Routes\RoutesManager::class,
        'assets' => OffbeatWP\Assets\AssetsManager::class
    ]));
}