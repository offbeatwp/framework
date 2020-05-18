<?php
namespace OffbeatWP\Support\Wordpress;

class RestApi {
    public static function isRestApiRequest()
    {
        return defined('REST_REQUEST');
    }
}