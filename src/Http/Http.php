<?php
namespace OffbeatWP\Http;

class Http
{
    public function abort($code, $description = '') {
        global $post, $wp_query;

        status_header($code, $description);

        return apply_filters('ofbeatwp/http_status', null, $code);

        // query_posts(['page_id' => 4307]);
        // the_post();

        // $route = offbeat('routes')->findMatch();

        // return offbeat()->runRoute($route);
    }
}
