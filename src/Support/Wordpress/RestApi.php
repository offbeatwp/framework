<?php
namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Api\RestEndpointBuilder;

class RestApi {
    public static function isRestApiRequest()
    {
        return defined('REST_REQUEST');
    }

    public static function make(string $namespace, string $route, string $callback) {
        return new RestEndpointBuilder($namespace, $route, $callback);
    }
}