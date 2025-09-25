<?php

namespace OffbeatWP\Api;

use WP_REST_Server;

final class RestEndpointBuilder
{
    public readonly string $namespace;
    public readonly string $route;
    /** @var callable */
    public $callback;
    public string $method = WP_REST_Server::READABLE;
    /** @var array<string, mixed[]> */
    public array $args = [];
    /** @var callable */
    public $permissionCallback = '__return_true';

    public function __construct(string $namespace, string $route, callable $callback)
    {
        $this->namespace = $namespace;
        $this->route = $route;
        $this->callback = $callback;
    }

    /** @return $this */
    public function method(string $method): static
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Validate a particular endpoint argument against a callback.
     * @return $this
     */
    public function validate(string $key, callable $callback): static
    {
        if (!isset($this->args[$key])) {
            $this->args[$key] = [];
        }

        $this->args[$key]['validate_callback'] = $callback;

        return $this;
    }

    /**
     * Adds a callback that checks if the user can perform the action (reading, updating, etc) before the real callback is called.<br>
     * This allows the API to tell the client what actions they can perform on a given URL without needing to attempt the request first.<br>
     * The permissions callback is run after remote authentication, which sets the current user.
     * @return $this
     */
    public function permission(callable $callback): static
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

    public static function get(string $namespace, string $route, callable $callback): static
    {
        return new static($namespace, $route, $callback);
    }

    public static function post(string $namespace, string $route, callable $callback): static
    {
        return (new static($namespace, $route, $callback))->method(WP_REST_Server::CREATABLE);
    }

    public static function delete(string $namespace, string $route, callable $callback): static
    {
        return (new static($namespace, $route, $callback))->method(WP_REST_Server::DELETABLE);
    }
}
