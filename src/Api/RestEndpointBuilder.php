<?php

namespace OffbeatWP\Api;

use Closure;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

class RestEndpointBuilder
{
    public string $namespace;
    public string $route;
    /** @var callable(WP_REST_Request): mixed */
    public $callback;
    public string $method = WP_REST_Server::READABLE;
    /** @var Closure[] */
    public array $args = [];
    /** @var callable */
    public $permissionCallback = '__return_true';

    /**
     * @param string $namespace
     * @param string $route
     * @param callable(WP_REST_Request): mixed $callback
     */
    final public function __construct(string $namespace, string $route, callable $callback)
    {
        $this->namespace = $namespace;
        $this->route = $route;
        $this->callback = $callback;
    }

    public function method(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param string $key
     * @param callable(string, WP_Rest_Request, string): bool $callback
     * @return $this
     */
    public function validate(string $key, callable $callback): self
    {
        if (!isset($this->args[$key])) {
            $this->args[$key] = [];
        }

        $this->args[$key]['validate_callback'] = $callback;

        return $this;
    }

    public function capability(string $capability): self
    {
        $this->permission(fn() => current_user_can($capability));
        return $this;
    }

    /**
     * @param callable(WP_Rest_Request): (bool|WP_Error) $callback
     * @return $this
     */
    public function permission(callable $callback): self
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

    final public static function get(string $namespace, string $route, callable $callback): self
    {
        return new static($namespace, $route, $callback);
    }

    final public static function post(string $namespace, string $route, callable $callback): self
    {
        return (new static($namespace, $route, $callback))->method(WP_REST_Server::CREATABLE);
    }

    final public static function delete(string $namespace, string $route, callable $callback): self
    {
        return (new static($namespace, $route, $callback))->method(WP_REST_Server::DELETABLE);
    }
}