<?php
namespace OffbeatWP\Api;

use Closure;
use WP_REST_Request;

class RestEndpointBuilder
{
    public $namespace = null;
    public $route = null;
    public $callback = null;
    public $method = 'GET';
    public $args = [];
    public $permission_callback = '__return_true';

    public function __construct(string $namespace, string $route, string $callback)
    {
        $this->namespace = $namespace;
        $this->route = $route;
        $this->callback = $callback;

    }

    public function method(string $method):RestEndpoint {
        $this->method = $method;

        return $this;
    }

    public function validate(string $key, Closure $callback):RestEndpoint {
        if (!isset($this->args[$key])) {
            $this->args[$key] = [];
        }

        $this->args[$key]['validate_callback'] = $callback;

        return $this;
    }

    public function capability(string $capability):RestEndpoint {
        $this->permission(function () use ($capability) {
            return current_user_can($capability);
        });

        return $this;
    }

    public function permission(Closure $callback):RestEndpoint {
        $this->permission_callback = $callback;

        return $this;
    }

    public function set()
    {
        $restEndpoint = $this;

        add_action( 'rest_api_init', function () use ($restEndpoint) {
            register_rest_route($restEndpoint->namespace , $restEndpoint->route, [
                'methods' => $restEndpoint->method,
                'callback' => function (WP_REST_Request $request) use ($restEndpoint) {
                    return (new $restEndpoint->callback($request))->response();
                },
                'args' => $restEndpoint->args,
                'permission_callback' => $restEndpoint->permission_callback,
            ]);
          } );
    }
}