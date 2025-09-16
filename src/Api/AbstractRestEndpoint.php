<?php

namespace OffbeatWP\Api;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/** @template T of array */
abstract class AbstractRestEndpoint
{
    /** @var WP_REST_Request<T> */
    protected WP_REST_Request $request;

    /** @param WP_REST_Request<T> $request */
    public function __construct(WP_REST_Request $request)
    {
        $this->request = $request;
    }

    /** @return WP_REST_Request<T> */
    public function getRequest(): WP_REST_Request
    {
        return $this->request;
    }

    abstract public function response(): WP_REST_Response|WP_Error;
}
