<?php

namespace OffbeatWP\Tools\BreadcrumbNavXT\Helpers;

class ViewHelpers {
    public function display($linked = true, $reverse = false, $force = false)
    {
        if (!function_exists('bcn_display')) {
            return __('Breadcrumb NavXT is not installed');
        }

        return \bcn_display(true, $linked, $reverse, $force);
    }
}