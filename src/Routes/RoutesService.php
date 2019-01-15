<?php
namespace OffbeatWP\Routes;

use OffbeatWP\Services\AbstractService;

class RoutesService extends AbstractService
{
    public $bindings = [
        'routes' => RoutesManager::class,
    ];

    public function register()
    {
        add_action('before_route_matching', [$this, 'loadRoutes'], 20);
    }

    protected function loadRoutes()
    {
        $routeFiles = glob($this->app->routesPath() . '/*.php');

        foreach ($routeFiles as $routeFile) {
            require $routeFile;
        }
    }
}
