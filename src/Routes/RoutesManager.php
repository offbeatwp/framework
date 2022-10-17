<?php

namespace OffbeatWP\Routes;

use Closure;
use Exception;
use Illuminate\Support\Collection;
use OffbeatWP\Exceptions\InvalidRouteException;
use OffbeatWP\Routes\Routes\CallbackRoute;
use OffbeatWP\Routes\Routes\PathRoute;
use OffbeatWP\Routes\Routes\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class RoutesManager
{
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_FIXED = 'fixed';

    /** @var Collection */
    protected $actions;
    /** @var RouteCollection */
    protected $routeCollection;
    /** @var null */
    protected $routesContext;
    /** @var int */
    protected $routeIterator = 0;
    /** @var Route|null|false */
    protected $lastMatchRoute;

    protected bool $routesAdded = false;
    protected array $routes = [
        self::PRIORITY_LOW => [],
        self::PRIORITY_HIGH => [],
        self::PRIORITY_FIXED => []
    ];

    public function __construct()
    {
        $this->routeCollection = new RouteCollection();
        $this->actions = collect();
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->routeCollection;
    }

    public function createCallback($checkCallback, $actionCallback, $parameters = [], array $options = []): Route
    {
        return $this->createRoute($checkCallback, $actionCallback, $parameters, [], $options);
    }

    public function callback($checkCallback, $actionCallback, $parameters = [], array $options = []): Route
    {
        $routeObj = $this->createCallback($checkCallback, $actionCallback, $parameters, $options);
        $this->addRoute($routeObj);
        return $routeObj;
    }

    public function createGet($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['GET']);
    }

    public function get($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        $routeObj = $this->createGet($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($routeObj);
        return $routeObj;
    }

    public function createPost(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['POST']);
    }

    public function post(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): Route
    {
        $routeObj = $this->createPost($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($routeObj);
        return $routeObj;
    }

    public function createPut(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['PUT']);
    }

    public function put(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): Route
    {
        $routeObj = $this->createPut($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($routeObj);
        return $routeObj;
    }

    public function createPatch($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['PATCH']);
    }

    public function patch($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        $routeObj = $this->createPatch($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($routeObj);
        return $routeObj;
    }

    public function createDelete($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['DELETE']);
    }

    public function delete($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        $routeObj = $this->createDelete($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($routeObj);
        return $routeObj;
    }

    /** @param string|Closure $target */
    public function createRoute($target, $actionCallback, $defaults, array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = ''): Route
    {
        $name = $this->getNextRouteName();

        $routeClass = PathRoute::class;
        if (!is_string($target)) {
            $routeClass = CallbackRoute::class;
        }

        if ($defaults instanceof Closure) {
            $defaults = ['_parameterCallback' => $defaults];
        }

        return new $routeClass($name, $target, $actionCallback, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
    }

    /** Add a route to the stack based on it's priority. If no priority is set, the default priority is PRIORITY_HIGH. */
    public function addRoute(Route $route, string $priority = self::PRIORITY_HIGH): RoutesManager
    {
        if ($priority === self::PRIORITY_HIGH) {
            $this->routes[self::PRIORITY_HIGH][] = $route;
        } elseif ($priority === self::PRIORITY_LOW) {
            $this->routes[self::PRIORITY_LOW][] = $route;
        } else {
            $this->routes[self::PRIORITY_FIXED][] = [$priority, $route];
        }

        $this->routesAdded = false;

        return $this;
    }

    protected function addRoutes(): self
    {
        if ($this->routesAdded) {
            return $this;
        }

        // Symfony will reverse the routes again
        $routes = array_merge(
            array_reverse($this->routes[self::PRIORITY_LOW]),
            $this->routes[self::PRIORITY_HIGH]
        );

        // adding fixed priority routes
        foreach ($this->routes[self::PRIORITY_FIXED] as $routeData) {
            array_splice($routes, $routeData[0], 0, [$routeData[1]]);
        }

        $this->getRouteCollection()->removeAll();

        foreach ($routes as $priority => $route) {
            /** @var Route $route */
            $this->getRouteCollection()->add($route->getName(), $route, $priority);
        }

        $this->routesAdded = true;

        return $this;
    }

    public function findRoute()
    {
        $this->addRoutes();

        $route = $this->findPathRoute();

        if (!$route) {
            $route = $this->findCallbackRoute();
        }

        $this->lastMatchRoute = $route;

        return $route;
    }

    public function findPathRoute()
    {
        $this->addRoutes();

        // $request = Request::createFromGlobals(); // Disabled, gave issues with uploads
        $request = Request::create($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $_REQUEST, $_COOKIE, [], $_SERVER);

        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->getRouteCollection()->findByType(PathRoute::class), $context);

        try {
            $parameters = $matcher->match($request->getPathInfo());

            if (!apply_filters('offbeatwp/route/match/url', true, $matcher)) {
                throw new InvalidRouteException('Route does not match (override)');
            }

            $route = $this->getRouteCollection()->get($parameters['_route']);
            $route->addDefaults($parameters);

            return $route;
        } catch (Exception $e) {
            return false;
        }
    }

    public function findCallbackRoute()
    {
        $this->addRoutes();

        $callbackRoutes = $this->getRouteCollection()->findByType(CallbackRoute::class);

        /** @var CallbackRoute $callbackRoute */
        foreach ($callbackRoutes->all() as $callbackRoute) {
            if (apply_filters('offbeatwp/route/match/wp', true, $callbackRoute) && $callbackRoute->doMatchCallback()) {
                return $callbackRoute;
            }
        }

        return false;
    }

    /** @param Route $route */
    public function removeRoute($route)
    {
        $this->addRoutes();

        if ($route instanceof Route) {
            $routeName = $route->getName();

            if ($route->getOption('persistent') === true) {
                return;
            }

            $this->getRouteCollection()->remove($routeName);
        }
    }

    public function getLastMatchRoute()
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