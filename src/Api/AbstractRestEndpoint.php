<?php
namespace OffbeatWP\Api;

use WP_REST_Request;

abstract class AbstractRestEndpoint
{
    protected $request;

    public function __construct(WP_REST_Request $request) {
        $this->request = $request;
    }

    public function getRequest() {
        return $this->request;
    }

    abstract public function response();
}