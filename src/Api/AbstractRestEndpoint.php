<?php
namespace OffbeatWP\Api;

use WP_REST_Request;

/** @template T of array */
abstract class AbstractRestEndpoint
{
    /** @var WP_REST_Request<T> */
    protected $request;

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

    /** @return T */
    abstract public function response();
}