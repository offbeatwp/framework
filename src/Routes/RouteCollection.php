<?php
namespace OffbeatWP\Routes;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

class RouteCollection extends SymfonyRouteCollection
{
    /** @param SymfonyRoute[] $routes */
    public function __construct(iterable $routes = []) {
        foreach ($routes as $routeName => $route) {
            $this->add($routeName, $route);
        }
    }

    public function getOrFail(string $name): SymfonyRoute
    {
        $route = $this->get($name);

        if (!$route) {
            throw new RouteNotFoundException('Could not find route: ' . $name);
        }

        return $route;
    }

    public function findByType(string $type): RouteCollection
    {
        return $this->where('type', $type);
    }

    // TODO: Does this method do anything if whereKey isn't type, and won't this dupe all routes?
    public function where(string $whereKey, string $whereValue): RouteCollection
    {
        $routes = array_filter($this->all(), static function (SymfonyRoute $route) use ($whereKey, $whereValue) {
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
