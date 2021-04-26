<?php
namespace OffbeatWP\Routes;

use Symfony\Component\Routing\RouteCollection as RoutingRouteCollection;

class RouteCollection extends RoutingRouteCollection
{
    public function __construct (array $routes = []) {
        if (!empty($routes)) {
            $this->routes = $routes;
        }
    }

    public function findByType(string $type) {
        return $this->where('type', $type);
    }

    public function where($whereKey, $whereValue) {
        $routes = array_filter($this->all(), function ($route) use ($whereKey, $whereValue) {
            switch($whereKey) {
                case 'type':
                    if ($route->getType() == $whereValue) return true;
                    break;
            }

            return false;
        });

        $filteredRouteCollection = new self($routes);

        if(!empty($routes)) foreach ($routes as $routeName => $route) {
            $filteredRouteCollection->add($routeName, $route);
        }

        return $filteredRouteCollection;
    }
}
