<?php
namespace OffbeatWP\Http;

class Http
{
    public function abort($code, $description = '') {
        global $post, $wp_query;

        status_header($code, $description);

        return apply_filters('offbeatwp/http_status', null, $code);
    }
}
