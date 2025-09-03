<?php

namespace OffbeatWP\Assets;

use OffbeatWP\Services\AbstractService;

final class ServiceEnqueueScripts extends AbstractService
{
    public function register(): void
    {
        if (!is_admin()) {
            add_action('wp_enqueue_scripts', [$this, 'enqueueScripts'], 1);
        }
    }

    public function enqueueScripts(): void
    {
        if (apply_filters('offbeatwp/assets/include_main_script_by_default', true)) {
            AssetsManager::getInstance()->enqueueScripts('main');
        }

        if (apply_filters('offbeatwp/assets/include_main_style_by_default', true)) {
            AssetsManager::getInstance()->enqueueStyles('main');
        }
    }
}
