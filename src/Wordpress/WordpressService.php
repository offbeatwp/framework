<?php

namespace OffbeatWP\Wordpress;

use OffbeatWP\Services\AbstractService;

final class WordpressService extends AbstractService
{
    public function register(): void
    {
        add_action('after_setup_theme', [$this, 'registerMenus']);
        $this->registerImageSizes();
        $this->registerSidebars();
    }

    public function registerMenus(): void
    {
        $menus = config('menus', false);

        if (is_array($menus) && $menus) {
            register_nav_menus($menus);
        }
    }

    public function registerImageSizes(): void
    {
        $images = config('images', false);

        if (is_array($images) && $images) {
            foreach ($images as $key => $image) {
                add_image_size($key, $image['width'], $image['height'], $image['crop']);
            }
        }
    }

    public function registerSidebars(): void
    {
        $sidebars = config('sidebars', false);

        if (is_array($sidebars) && $sidebars) {
            foreach ($sidebars as $id => $sidebar) {
                $sidebar['id'] = $id;
                register_sidebar($sidebar);
            }
        }
    }
}
