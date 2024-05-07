<?php

namespace OffbeatWP\Api;

use Closure;
use WP_REST_Request;
use WP_REST_Server;

final class RestEndpointBuilder
{
    public string $namespace;
    public string $route;
    /** @var callable(WP_REST_Request<mixed[]>): mixed */
    public $callback;
    public string $method = WP_REST_Server::READABLE;
    /** @var Closure[] */
    public array $args = [];
    /** @var callable(WP_Rest_Request<mixed[]>): (bool|\WP_Error) */
    public $permissionCallback = '__return_true';

    /** @param callable(WP_REST_Request<mixed[]>): (\WP_REST_Response|\WP_Error) $callback */
    final public function __construct(string $namespace, string $route, callable $callback)
    {
        $this->namespace = $namespace;
        $this->route = $route;
        $this->callback = $callback;
    }

    /** @return $this */
    final public function method(string $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param callable(string, WP_Rest_Request<mixed[]>, string): bool $callback
     * @return $this
     */
    final public function validate(string $key, callable $callback)
    {
        if (!isset($this->args[$key])) {
            $this->args[$key] = [];
        }

        $this->args[$key]['validate_callback'] = $callback;

        return $this;
    }

    /** @return $this */
    final public function capability(string $capability)
    {
        $this->permission(fn() => current_user_can($capability));
        return $this;
    }

    /**
     * @param callable(WP_Rest_Request<mixed[]>): (bool|\WP_Error) $callback
     * @return $this
     */
    final public function permission(callable $callback)
    {
        $this->permissionCallback = $callback;
        return $this;
    }

    final public function set(): void
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

    /**
     * @pure
     * @param callable(WP_REST_Request<mixed[]>): mixed $callback
     */
    final public static function get(string $namespace, string $route, callable $callback): static
    {
        return new static($namespace, $route, $callback);
    }

    /**
     * @pure
     * @param callable(WP_REST_Request<mixed[]>): mixed $callback
     */
    final public static function post(string $namespace, string $route, callable $callback): static
    {
        return (new static($namespace, $route, $callback))->method(WP_REST_Server::CREATABLE);
    }

    /**
     * @pure
     * @param callable(WP_REST_Request<mixed[]>): mixed $callback
     */
    final public static function delete(string $namespace, string $route, callable $callback): static
    {
        return (new static($namespace, $route, $callback))->method(WP_REST_Server::DELETABLE);
    }
}