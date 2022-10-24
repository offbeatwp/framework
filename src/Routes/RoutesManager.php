<?php

namespace OffbeatWP\Routes;

use Closure;
use Exception;
use OffbeatWP\Exceptions\InvalidRouteException;
use OffbeatWP\Routes\Routes\CallbackRoute;
use OffbeatWP\Routes\Routes\PathRoute;
use OffbeatWP\Routes\Routes\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class RoutesManager
{
    protected $actions;
    protected $routeCollection;
    protected $routesContext;
    protected $routeIterator = 0;
    protected $lastMatchRoute;

    protected string $priorityMode = '';

    protected array $routes = [];
    protected bool $routesAdded = false;

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_FIXED = 'fixed';

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
            throw new \InvalidArgumentException("Cannot set priority mode");
        }
        $this->priorityMode = $mode;
        return $this;
    }

    public function createCallback($checkCallback, $actionCallback, $parameters = [], array $options = []): Route
    {
        return $this->createRoute($checkCallback, $actionCallback, $parameters, [], $options);
    }

    public function callback($checkCallback, $actionCallback, $parameters = [], array $options = []): Route
    {
        $route = $this->createCallback($checkCallback, $actionCallback, $parameters, $options);
        $this->addRoute($route);
        return $route;
    }

    public function createGet($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['GET']);
    }

    public function get($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        $route = $this->createGet($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($route);
        return $route;
    }

    public function createPost(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['POST']);
    }

    public function post(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): Route
    {
        $route = $this->createPost($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($route);
        return $route;
    }

    public function createPut(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['PUT']);
    }

    public function put(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = []): Route
    {
        $route = $this->createPut($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($route);
        return $route;
    }

    public function createPatch($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['PATCH']);
    }

    public function patch($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        $route = $this->createPatch($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($route);
        return $route;
    }

    public function createDelete($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        return $this->createRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['DELETE']);
    }

    public function delete($route, $actionCallback, $parameters = [], array $requirements = [], array $options = []): Route
    {
        $route = $this->createDelete($route, $actionCallback, $parameters, $requirements, $options);
        $this->addRoute($route);
        return $route;
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

        return new $routeClass(
            $name, //name
            $target, // target
            $actionCallback,
            $defaults, // default values
            $requirements, // requirements
            $options, // options
            $host, // host
            $schemes, // schemes
            $methods, // methods
            $condition // condition
        );
    }

    /**
     * Add a route to the stack based on it's priority.
     * If no priority is set, the default priority is PRIORITY_HIGH.
     *
     * @param Route $route
     * @param $priority
     * @return $this
     */
    public function addRoute(Route $route, $priority = null): RoutesManager
    {
        if (!$this->routes) {
            $this->routes = [
                self::PRIORITY_LOW => [],
                self::PRIORITY_HIGH => [],
                self::PRIORITY_FIXED => []
            ];
        }
        if ($priority === null) {
            if ($this->priorityMode) {
                $priority = $this->priorityMode;
            } else {
                $priority = self::PRIORITY_HIGH;
            }
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