<?php

namespace OffbeatWP\Wordpress;

use OffbeatWP\Services\AbstractService;

final class WordpressService extends AbstractService
{
    public function register(): void
    {
        add_action('after_setup_theme', [$this, 'registerMenus']);
        add_action('after_setup_theme', [$this, 'registerImageSizes']);
    }

    public function registerMenus(): void
    {
        $menus = config('menus', false);

        if (is_array($menus) && $menus) {
            /** @var array<string, string> $menus */
            register_nav_menus($menus);
        }
    }

    public function registerImageSizes(): void
    {
        $images = config('images', false);

        if (is_array($images)) {
            /** @var array{width: int, height: int, crop: array{0: string, 1: string}} $image */
            foreach ($images as $key => $image) {
                add_image_size((string)$key, $image['width'], $image['height'], $image['crop']);
            }
        }
    }
}
