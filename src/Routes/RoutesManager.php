<?php

namespace OffbeatWP\Routes;

use Closure;
use Exception;
use InvalidArgumentException;
use OffbeatWP\Exceptions\InvalidRouteException;
use OffbeatWP\Routes\Routes\CallbackRoute;
use OffbeatWP\Routes\Routes\PathRoute;
use OffbeatWP\Routes\Routes\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

final class RoutesManager
{
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_FIXED = 'fixed';

    protected RouteCollection $routeCollection;
    protected int $routeIterator = 0;
    protected PathRoute|CallbackRoute|false|null $lastMatchRoute;

    protected string $priorityMode = self::PRIORITY_HIGH;
    protected bool $routesAdded = false;
    /** @var Route[][] */
    protected array $routes = [
        self::PRIORITY_LOW => [],
        self::PRIORITY_HIGH => [],
        self::PRIORITY_FIXED => []
    ];

    public function __construct()
    {
        $this->routeCollection = new RouteCollection();
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->routeCollection;
    }

    /** @return $this */
    public function setPriorityMode(string $mode)
    {
        if (!in_array($mode, [self::PRIORITY_LOW, self::PRIORITY_HIGH])) {
            throw new InvalidArgumentException('Cannot set priority mode');
        }

        $this->priorityMode = $mode;
        return $this;
    }

    /**
     * @param string|Closure $checkCallback
     * @param callable $actionCallback
     * @param mixed[]|Closure $parameters
     * @param mixed[] $options
     * @return Route
     */
    public function createCallbackRoute($checkCallback, $actionCallback, $parameters = [], array $options = []): Route
    {
        return $this->createRoute($checkCallback, $actionCallback, $parameters, [], $options);
    }

    /**
     * @param string|Closure $checkCallback
     * @param callable $actionCallback
     * @param mixed[]|Closure $parameters
     * @param mixed[] $options
     * @return Route
     */
    public function callback($checkCallback, $actionCallback, $parameters = [], array $options = []): Route
    {
        $route = $this->createCallbackRoute($checkCallback, $actionCallback, $parameters, $options);
        $this->addRoute($route);
        return $route;
    }

    /**
     * @param string $route
     * @param callable $actionCallback
     * @param mixed[] $parameters
     * @param mixed[] $requirements
     * @param mixed[] $options
     * @return Route
     */
    public function createGetRoute($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['GET']);
    }

    /**
     * @param string|Closure $route
     * @param callable $actionCallback
     * @param mixed[] $parameters
     * @param mixed[] $requirements
     * @param mixed[] $options
     * @return Route
     */
    public function get($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        $routeObj = $this->createGetRoute($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($routeObj);
        return $routeObj;
    }
    /**
     * @param string $route
     * @param callable $actionCallback
     * @param mixed[] $parameters
     * @param mixed[] $requirements
     * @param mixed[] $options
     * @return Route
     */

    public function createPostRoute(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['POST']);
    }

    /**
     * @param string $route
     * @param callable $actionCallback
     * @param mixed[] $parameters
     * @param mixed[] $requirements
     * @param mixed[] $options
     * @return Route
     */
    public function post(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): Route
    {
        $routeObj = $this->createPostRoute($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($routeObj);
        return $routeObj;
    }

    /**
     * @param string $route
     * @param callable $actionCallback
     * @param mixed[] $parameters
     * @param mixed[] $requirements
     * @param mixed[] $options
     * @return Route
     */
    public function createPutRoute(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['PUT']);
    }

    /**
     * @param string $route
     * @param callable $actionCallback
     * @param mixed[] $parameters
     * @param mixed[] $requirements
     * @param mixed[] $options
     * @return Route
     */
    public function put(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): Route
    {
        $routeObj = $this->createPutRoute($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($routeObj);
        return $routeObj;
    }

    /**
     * @param string|Closure $route
     * @param callable $actionCallback
     * @param mixed[]|Closure $parameters
     * @param mixed[] $requirements
     * @param mixed[] $options
     * @return Route
     */
    public function createPatchRoute($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['PATCH']);
    }

    /**
     * @param string|Closure $route
     * @param callable $actionCallback
     * @param mixed[]|Closure $parameters
     * @param mixed[] $requirements
     * @param mixed[] $options
     * @return Route
     */
    public function patch($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        $routeObj = $this->createPatchRoute($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($routeObj);
        return $routeObj;
    }

    /**
     * @param string|Closure $route
     * @param callable $actionCallback
     * @param mixed[]|Closure $parameters
     * @param mixed[] $requirements
     * @param mixed[] $options
     * @return Route
     */
    public function createDeleteRoute($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['DELETE']);
    }

    /**
     * @param string|Closure $route
     * @param callable $actionCallback
     * @param mixed[]|Closure $parameters
     * @param mixed[] $requirements
     * @param mixed[] $options
     * @return Route
     */
    public function delete($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        $routeObj = $this->createDeleteRoute($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($routeObj);
        return $routeObj;
    }

    /**
     * @param string|Closure $target
     * @param callable $actionCallback
     * @param mixed[]|Closure $defaults
     * @param mixed[] $requirements
     * @param mixed[] $options
     * @param string|null $host
     * @param string[] $schemes
     * @param array<"GET"|"POST"|"PUT"|"DELETE"|"PATCH"> $methods
     * @param string|null $condition
     * @return PathRoute|CallbackRoute
     */
    public function createRoute($target, $actionCallback, $defaults, array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = ''): PathRoute|CallbackRoute
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

    /**
     * Add a route to the stack based on it's priority.<br>
     * If no priority is set, the default priority is the priority set via the priority mode method.
     * @param Route $route
     * @param ""|"low"|"high"|"fixed" $priority
     * @return $this
     */
    public function addRoute(Route $route, string $priority = '')
    {
        if (!$priority) {
            $priority = $this->priorityMode;
        }

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

    /** @return $this */
    public function addRoutes()
    {
        if ($this->routesAdded) {
            return $this;
        }

        // Symfony will reverse the routes again
        $routes = array_merge(
            $this->routes[self::PRIORITY_LOW],
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

    /** @return PathRoute|CallbackRoute|false */
    public function findRoute()
    {
        $route = $this->findPathRoute();

        if (!$route) {
            $route = $this->findCallbackRoute();
        }

        $this->lastMatchRoute = $route;

        return $route;
    }

    public function findPathRoute(): PathRoute|null|false
    {
        // $request = Request::createFromGlobals(); // Disabled, gave issues with uploads
        $request = Request::create($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'] ?? 'GET', $_REQUEST, $_COOKIE, [], $_SERVER);

        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->getRouteCollection()->findByType(PathRoute::class), $context);

        try {
            $parameters = $matcher->match($request->getPathInfo());

            if (!apply_filters('offbeatwp/route/match/url', true, $matcher)) {
                throw new InvalidRouteException('Route does not match (override)');
            }

            /** @var PathRoute|null $route */
            $route = $this->getRouteCollection()->get($parameters['_route']);
            $route->addDefaults($parameters);

            return $route;
        } catch (Exception) {
            return false;
        }
    }

    /** @return CallbackRoute|false */
    public function findCallbackRoute()
    {
        $callbackRoutes = $this->getRouteCollection()->findByType(CallbackRoute::class);

        /** @var CallbackRoute $callbackRoute */
        foreach ($callbackRoutes->all() as $callbackRoute) {
            if (apply_filters('offbeatwp/route/match/wp', true, $callbackRoute) && $callbackRoute->doMatchCallback()) {
                return $callbackRoute;
            }
        }

        return false;
    }

    public function removeRoute(CallbackRoute|PathRoute $route): void
    {
        $routeName = $route->getName();

        if ($route->getOption('persistent') === true) {
            return;
        }

        $this->getRouteCollection()->remove($routeName);
    }

    /** @return PathRoute|CallbackRoute|false */
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