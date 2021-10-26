<?php

namespace OffbeatWP\Routes;

use Closure;
use OffbeatWP\Exceptions\InvalidRouteException;
use OffbeatWP\Routes\Routes\CallbackRoute;
use OffbeatWP\Routes\Routes\PathRoute;
use OffbeatWP\Routes\Routes\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route as SymfonyRoute;

class RoutesManager
{
    protected $actions;
    protected $routeCollection;
    protected $routesContext; //TODO: Is this parameter used?
    protected $routeIterator = 0;
    protected $lastMatchRoute;

    public function __construct()
    {
        $this->routeCollection = new RouteCollection();
        $this->actions = collect();
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->routeCollection;
    }

    /** Callbacks are executed in LiFo order. */
    public function callback($checkCallback, $actionCallback, $parameters = [], array $options = []): void
    {
        $this->addRoute($checkCallback, $actionCallback, $parameters, [], $options);
    }

    public function get($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): void
    {
        $this->addRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['GET']);
    }

    public function post(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): void
    {
        $this->addRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['POST']);
    }

    public function put(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): void
    {
        $this->addRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['PUT']);
    }

    public function patch($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): void
    {
        $this->addRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['PATCH']);
    }

    public function delete($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): void
    {
        $this->addRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['DELETE']);
    }

    /** @param string|Closure $target */
    public function addRoute($target, $actionCallback, $defaults, array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = ''): void
    {
        $name = $this->getNextRouteName();

        $routeClass = is_string($target) ? PathRoute::class : CallbackRoute::class;

        if ($defaults instanceof Closure) {
            $defaults = ['_parameterCallback' => $defaults];
        }

        $route = new $routeClass(
            $name,
            $target,
            $actionCallback,
            $defaults,
            $requirements,
            $options,
            $host,
            $schemes,
            $methods,
            $condition
        );

        $this->getRouteCollection()->add($route->getName(), $route);
    }

    public function findRoute(): ?SymfonyRoute
    {
        $route = $this->findPathRoute();

        if (!$route) {
            $route = $this->findCallbackRoute();
        }

        $this->lastMatchRoute = $route;

        return $route;
    }

    public function findPathRoute(): ?SymfonyRoute
    {
        $request = Request::create($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $_REQUEST, $_COOKIE, [], $_SERVER);

        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->getRouteCollection()->findByType(PathRoute::class), $context);

        try {
            $parameters = $matcher->match($request->getPathInfo());

            if (!apply_filters('offbeatwp/route/match/url', true, $matcher)) {
                throw new InvalidRouteException('Route does not match (override)');
            }

            $route = $this->getRouteCollection()->getOrFail($parameters['_route']);

            $route->addDefaults($parameters);

            return $route;
        } catch (InvalidRouteException $e) {
            return null;
        }
    }

    public function findCallbackRoute(): ?SymfonyRoute
    {
        $callbackRoutes = $this->getRouteCollection()->findByType(CallbackRoute::class);

        /** @var CallbackRoute $callbackRoute */
        foreach ($callbackRoutes->all() as $callbackRoute) {
            $filteredRoute = apply_filters('offbeatwp/route/match/wp', true, $callbackRoute);

            if ($filteredRoute && $callbackRoute->doMatchCallback()) {
                return $callbackRoute;
            }
        }

        return null;
    }

    public function removeRoute(Route $route): void
    {
        if ($route->getOption('persistent') === true) {
            return;
        }

        $this->getRouteCollection()->remove($route->getName());
    }

    public function getLastMatchRoute(): ?SymfonyRoute
    {
        return $this->lastMatchRoute;
    }

    public function getNextRouteName(): string
    {
        $routeName = 'route' . $this->routeIterator;
        $this->routeIterator++;

        return $routeName;
    }
}
