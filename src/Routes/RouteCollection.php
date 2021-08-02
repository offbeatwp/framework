<?php
namespace OffbeatWP\Routes;

use Symfony\Component\Routing\RouteCollection as RoutingRouteCollection;

class RouteCollection extends RoutingRouteCollection
{
    public function __construct (array $routes = []) {
        if (!empty($routes)) {
            foreach ($routes as $key => $route) {
                $this->add($key, $route);
            }
        }
    }

    public function findByType(string $type): RouteCollection
    {
        return $this->where('type', $type);
    }

    public function where(string $whereKey, string $whereValue): RouteCollection
    {
        $routes = array_filter($this->all(), static function ($route) use ($whereKey, $whereValue) {
            return $whereKey === 'type' && $whereValue === get_class($route);
        });

        $filteredRouteCollection = new self($routes);

        if(!empty($routes)) {
            foreach ($routes as $routeName => $route) {
                $filteredRouteCollection->add($routeName, $route);
            }
        }

        return $filteredRouteCollection;
    }
}
