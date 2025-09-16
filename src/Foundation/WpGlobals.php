<?php

namespace OffbeatWP\Foundation;

use WP_Post;

final class WpGlobals
{
    public static function post(): ?WP_Post
    {
        /** @var WP_Post|null */
        return $GLOBALS['post'] ?? null;
    }
}
