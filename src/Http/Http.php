<?php

namespace OffbeatWP\Http;

class Http
{
    public function abort(int $code, string $description = '')
    {
        status_header($code, $description);

        return apply_filters('offbeatwp/http_status', null, $code);
    }

    public function redirect(string $url, int $status = 301)
    {
        wp_redirect($url, $status);
        exit;
    }
}
