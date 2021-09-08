<?php
namespace OffbeatWP\Api;

use WP_REST_Request;

abstract class AbstractRestEndpoint
{
    protected $request;

    public function __construct(WP_REST_Request $request)
    {
        $this->request = $request;
    }

    public function getRequest(): WP_REST_Request
    {
        return $this->request;
    }

    abstract public function response();
}