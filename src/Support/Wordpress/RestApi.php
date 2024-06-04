<?php
namespace OffbeatWP\Support\Wordpress;

use OffbeatWP\Api\RestEndpointBuilder;
use WP_REST_Request;

final class RestApi {
    public static function isRestApiRequest(): bool
    {
        return defined('REST_REQUEST');
    }

    public static function make(string $namespace, string $route, string $callbackStr): RestEndpointBuilder
    {
        return new RestEndpointBuilder($namespace, $route, function (WP_REST_Request $request) use ($callbackStr) {
            return (new $callbackStr($request))->response();
        });
    }
}