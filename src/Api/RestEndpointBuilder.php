<?php

namespace OffbeatWP\Api;

use WP_REST_Server;

final class RestEndpointBuilder
{
    public string $namespace;
    public string $route;
    /** @var callable(\WP_REST_Request<mixed[]>): (\WP_REST_Response|\WP_Error) */
    public $callback;
    public string $method = WP_REST_Server::READABLE;
    /** @var array<string, mixed[]> */
    public array $args = [];
    /** @var callable(\WP_REST_Request<mixed[]>): (bool|\WP_Error) */
    public $permissionCallback = '__return_true';

    /** @param callable(\WP_REST_Request<mixed[]>): mixed $callback */
    public function __construct(string $namespace, string $route, callable $callback)
    {
        $this->namespace = $namespace;
        $this->route = $route;
        $this->callback = $callback;
    }

    /** @return $this */
    public function method(string $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Validate a particular endpoint argument against a callback.
     * @param callable(string, \WP_REST_Request<mixed[]>, string): bool $callback
     * @return $this
     */
    public function validate(string $key, callable $callback)
    {
        if (!isset($this->args[$key])) {
            $this->args[$key] = [];
        }

        $this->args[$key]['validate_callback'] = $callback;

        return $this;
    }

    /**
     * @deprecated Use permission instead.
     * @return $this
     */
    public function capability(string $capability)
    {
        $this->permission(fn () => current_user_can($capability));
        return $this;
    }

    /**
     * Adds a callback that checks if the user can perform the action (reading, updating, etc) before the real callback is called.<br>
     * This allows the API to tell the client what actions they can perform on a given URL without needing to attempt the request first.<br>
     * The permissions callback is run after remote authentication, which sets the current user.
     * @param callable(\WP_REST_Request<mixed[]>): (bool|\WP_Error) $callback
     * @return $this
     */
    public function permission(callable $callback)
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

    /** @param callable(\WP_REST_Request<mixed[]>): (\WP_REST_Response|\WP_Error) $callback */
    public static function get(string $namespace, string $route, callable $callback): static
    {
        return new static($namespace, $route, $callback);
    }

    /** @param callable(\WP_REST_Request<mixed[]>): (\WP_REST_Response|\WP_Error) $callback */
    public static function post(string $namespace, string $route, callable $callback): static
    {
        return (new static($namespace, $route, $callback))->method(WP_REST_Server::CREATABLE);
    }

    /** @param callable(\WP_REST_Request<mixed[]>): (\WP_REST_Response|\WP_Error) $callback */
    public static function delete(string $namespace, string $route, callable $callback): static
    {
        return (new static($namespace, $route, $callback))->method(WP_REST_Server::DELETABLE);
    }
}
