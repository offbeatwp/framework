<?php

namespace OffbeatWP\Foundation;

use WP_Admin_Bar;
use WP_Post;

final class WpGlobals
{
    public static function post(): ?WP_Post
    {
        /** @var WP_Post|null */
        return $GLOBALS['post'] ?? null;
    }

    public static function wpAdminBar(): ?WP_Admin_Bar
    {
        return $GLOBALS['wp_admin_bar'] ?? null;
    }
}
