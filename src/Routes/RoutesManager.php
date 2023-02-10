<?php

namespace OffbeatWP\Routes;

use Closure;
use Exception;
use Illuminate\Support\Collection;
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

    protected Collection $actions;
    protected RouteCollection $routeCollection;
    protected int $routeIterator = 0;
    protected $lastMatchRoute;

    protected string $priorityMode = self::PRIORITY_HIGH;
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

    public function setPriorityMode(string $mode): self
    {
        if (!in_array($mode, [self::PRIORITY_LOW, self::PRIORITY_HIGH])) {
            throw new InvalidArgumentException('Cannot set priority mode');
        }

        $this->priorityMode = $mode;
        return $this;
    }

    public function createCallbackRoute($checkCallback, $actionCallback, $parameters = [], array $options = []): Route
    {
        return $this->createRoute($checkCallback, $actionCallback, $parameters, [], $options);
    }

    public function callback($checkCallback, $actionCallback, $parameters = [], array $options = []): Route
    {
        $route = $this->createCallbackRoute($checkCallback, $actionCallback, $parameters, $options);
        $this->addRoute($route);
        return $route;
    }

    public function createGetRoute($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['GET']);
    }

    public function get($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        $routeObj = $this->createGetRoute($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($routeObj);
        return $routeObj;
    }

    public function createPostRoute(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['POST']);
    }

    public function post(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): Route
    {
        $routeObj = $this->createPostRoute($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($routeObj);
        return $routeObj;
    }

    public function createPutRoute(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['PUT']);
    }

    public function put(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): Route
    {
        $routeObj = $this->createPutRoute($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($routeObj);
        return $routeObj;
    }

    public function createPatchRoute($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['PATCH']);
    }

    public function patch($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        $route = $this->createPatchRoute($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($route);
        return $route;
    }

    public function createDeleteRoute($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['DELETE']);
    }

    public function delete($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        $routeObj = $this->createDeleteRoute($route, $actionCallback, $parameters, $requirements, $options);
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

    /**
     * Add a route to the stack based on it's priority.<br>
     * If no priority is set, the default priority is the priority set via the priority mode method.
     */
    public function addRoute(Route $route, string $priority = ''): self
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

    public function addRoutes(): self
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

    public function findRoute()
    {
        $route = $this->findPathRoute();

        if (!$route) {
            $route = $this->findCallbackRoute();
        }

        $this->lastMatchRoute = $route;

        return $route;
    }

    public function findPathRoute()
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
        } catch (Exception $e) {
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

    /** @param Route $route */
    public function removeRoute($route)
    {
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