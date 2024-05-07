<?php
namespace OffbeatWP\Api;

use WP_Error;
use WP_REST_Request;

/** @template T */
abstract class AbstractRestEndpoint
{
    /** @var WP_REST_Request<T> */
    protected readonly WP_REST_Request $request;

    /** @param WP_REST_Request<T> $request */
    final public function __construct(WP_REST_Request $request)
    {
        $this->request = $request;
    }

    /** @return WP_REST_Request<T> */
    final public function getRequest(): WP_REST_Request
    {
        return $this->request;
    }

    abstract public function response(): WP_REST_Request|WP_Error;
}