<?php

namespace OffbeatWP\Wordpress;

use OffbeatWP\Support\Wordpress\AdminPage;
use OffbeatWP\Support\Wordpress\Ajax;
use OffbeatWP\Support\Wordpress\Console;
use OffbeatWP\Support\Wordpress\Design;
use OffbeatWP\Support\Wordpress\Post;
use OffbeatWP\Support\Wordpress\PostType;
use OffbeatWP\Support\Wordpress\RestApi;
use OffbeatWP\Support\Wordpress\Taxonomy;

final class WordpressService
{
    /** @var class-string[] */
    public array $bindings = [
        'admin-page' => AdminPage::class,
        'ajax'       => Ajax::class,
        'rest-api'   => RestApi::class,
        'console'    => Console::class,
        'post-type'  => PostType::class,
        'post'       => Post::class,
        'taxonomy'   => Taxonomy::class,
        'design'     => Design::class
    ];

    public function register(): void
    {
        add_action('after_setup_theme', [$this, 'registerMenus']);
        $this->registerImageSizes();
    }

    public function registerMenus(): void
    {
        $menus = config('menus');

        if (is_array($menus)) {
            register_nav_menus($menus);
        }
    }

    public function registerImageSizes(): void
    {
        $images = config('images');

        if (is_array($images)) {
            foreach ($images as $key => $image) {
                add_image_size($key, $image['width'], $image['height'], $image['crop']);
            }
        }
    }
}
