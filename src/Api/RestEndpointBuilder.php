<?php

namespace OffbeatWP\Api;

use Closure;
use WP_REST_Server;

class RestEndpointBuilder
{
    public $namespace;
    public $route;
    public $callback;
    public $method = WP_REST_Server::READABLE;
    public $args = [];
    public $permissionCallback = '__return_true';

    public function __construct(string $namespace, string $route, callable $callback)
    {
        $this->namespace = $namespace;
        $this->route = $route;
        $this->callback = $callback;
    }

    public function method(string $method): RestEndpointBuilder
    {
        $this->method = $method;

        return $this;
    }

    public function validate(string $key, Closure $callback): RestEndpointBuilder
    {
        if (!isset($this->args[$key])) {
            $this->args[$key] = [];
        }

        $this->args[$key]['validate_callback'] = $callback;

        return $this;
    }

    public function capability(string $capability): RestEndpointBuilder
    {
        $this->permission(function () use ($capability) {
            return current_user_can($capability);
        });

        return $this;
    }

    public function permission(Closure $callback): RestEndpointBuilder
    {
        $this->permissionCallback = $callback;

        return $this;
    }

    public function set(): void
    {
        $thisEndpoint = $this;

        add_action('rest_api_init', static function () use ($thisEndpoint) {
            register_rest_route($thisEndpoint->namespace, $thisEndpoint->route, [
                'methods' => $thisEndpoint->method,
                'callback' => $thisEndpoint->callback,
                'args' => $thisEndpoint->args,
                'permission_callback' => $thisEndpoint->permissionCallback,
            ]);
        });
    }

    public static function get(string $namespace, string $route, callable $callback)
    {
        return new static($namespace, $route, $callback);
    }

    public static function post(string $namespace, string $route, callable $callback)
    {
        return (new static($namespace, $route, $callback))->method(WP_REST_Server::CREATABLE);
    }

    public static function delete(string $namespace, string $route, callable $callback)
    {
        return (new static($namespace, $route, $callback))->method(WP_REST_Server::DELETABLE);
    }
}