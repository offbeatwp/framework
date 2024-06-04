<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\Enqueue\WpScriptEnqueueBuilder;
use OffbeatWP\Content\Enqueue\WpStyleEnqueueBuilder;

final class WpAsset
{
    public static function js(): WpScriptEnqueueBuilder
    {
        return new WpScriptEnqueueBuilder();
    }

    public static function css(): WpStyleEnqueueBuilder
    {
        return new WpStyleEnqueueBuilder();
    }
}