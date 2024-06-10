<?php

namespace OffbeatWP\Routes;

use OffbeatWP\Routes\Routes\Route;

final class RouteRequest
{
    private Route $route;

    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    public function getRoute(): Route
    {
        return $this->route;
    }
}
