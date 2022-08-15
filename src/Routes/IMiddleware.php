<?php

namespace OffbeatWP\Routes;

interface IMiddleware
{
    /**
     * If the middle passes, return the WpRouteRequest object.<br>
     * You can also return WpRedirect instead, to instead redirect the user elsewhere
     * @param RouteRequest $request
     * @return RouteRequest|WpRedirect
     */
    public function handle(RouteRequest $request);
}