<?php

namespace OffbeatWP\Routes;

use OffbeatWP\Routes\Routes\Route as OffbeatRoute;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

/**
 * @method OffbeatRoute[] all()
 * @method OffbeatRoute|null get(string $name)
 */
final class RouteCollection extends SymfonyRouteCollection
{
    /** @param OffbeatRoute[] $routes */
    public function __construct(array $routes = [])
    {
        foreach ($routes as $name => $route) {
            $this->add($name, $route);
        }
    }

    /** @return $this */
    public function removeAll()
    {
        foreach ($this->all() as $route) {
            $this->remove($route->getName());
        }

        return $this;
    }

    public function findByType(string $type): RouteCollection
    {
        return $this->where('type', $type);
    }

    /**
     * @param string $whereKey
     * @param class-string $whereValue
     * @return RouteCollection
     */
    public function where($whereKey, $whereValue): RouteCollection
    {
        $routes = array_filter($this->all(), static function ($route) use ($whereKey, $whereValue) {
            return $whereKey === 'type' && $route::class === $whereValue;
        });

        $filteredRouteCollection = new self($routes);

        if ($routes) {
            foreach ($routes as $routeName => $route) {
                $filteredRouteCollection->add($routeName, $route);
            }
        }

        return $filteredRouteCollection;
    }
}
