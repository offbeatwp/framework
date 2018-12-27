<?php

namespace OffbeatWP\Tools\Bootstrap;

use OffbeatWP\Services\AbstractService;

class Service extends AbstractService
{
    public function register()
    {
        add_filter('wp_pagenavi', [$this, 'pageNavigation'], 10, 2);
    }

    public static function pageNavigation($html)
    {
        $out = str_replace('<div', '', $html);
        $out = str_replace('class=\'wp-pagenavi\'>', '', $out);
        $out = str_replace('<a', '<li class="page-item"><a class="page-link"', $out);
        $out = str_replace('</a>', '</a></li>', $out);
        $out = str_replace('<span class=\'current\'', '<li class="page-item active"><span class="page-link current"',
            $out);
        $out = str_replace('<span class=\'pages\'', '<li class="page-item"><span class="page-link pages"', $out);
        $out = str_replace('<span class=\'extend\'', '<li class="page-item"><span class="page-link extend"', $out);
        $out = str_replace('</span>', '</span></li>', $out);
        $out = str_replace('</div>', '', $out);

        return '<ul class="pagination">' . $out . '</ul>';
    }
}