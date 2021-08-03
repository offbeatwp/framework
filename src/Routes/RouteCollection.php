<?php
namespace OffbeatWP\Routes;

use Symfony\Component\Routing\RouteCollection as RoutingRouteCollection;

class RouteCollection extends RoutingRouteCollection
{
    public function __construct (array $routes = []) {
        foreach ($routes as $name => $route) {
            $this->add($name, $route);
        }
    }

    public function findByType(string $type): RouteCollection
    {
        return $this->where('type', $type);
    }

    public function where($whereKey, $whereValue): RouteCollection
    {
        $routes = array_filter($this->all(), static function ($route) use ($whereKey, $whereValue) {
            return $whereKey === 'type' && get_class($route) === $whereValue;
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
