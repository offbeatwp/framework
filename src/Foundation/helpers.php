<?php
if (!function_exists('offbeatWP')) {
    function offbeat($service = null) {
        if (!is_null($service)) {
            return container($service);
        }

        return \OffbeatWP\Foundation\App::singleton();
    }
}