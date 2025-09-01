<?php

namespace OffbeatWP\Api;

use WP_Error;
use WP_REST_Request;

/** @template T of array */
abstract class AbstractRestEndpoint
{
    /** @var WP_REST_Request<T> */
    public readonly WP_REST_Request $request;

    /** @param WP_REST_Request<T> $request */
    public function __construct(WP_REST_Request $request)
    {
        $this->request = $request;
    }

    abstract public function response(): WP_REST_Request|WP_Error;
}
