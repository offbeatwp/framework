<?php

namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Content\Enqueue\WpScriptEnqueueBuilder;
use OffbeatWP\Content\Enqueue\WpStyleEnqueueBuilder;

class WpAsset
{
    final public static function js(): WpScriptEnqueueBuilder
    {
        return new WpScriptEnqueueBuilder();
    }

    final public static function css(): WpStyleEnqueueBuilder
    {
        return new WpStyleEnqueueBuilder();
    }
}