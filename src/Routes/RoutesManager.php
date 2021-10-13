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
    public function callback($checkCallback, $actionCallback, $parameters = [], array $options = [])
    {
        $this->addRoute($checkCallback, $actionCallback, $parameters, [], $options);
    }

    public function get($route, $actionCallback, $parameters = [], array $requirements = [], array $options = [])
    {
        $this->addRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['GET']);
    }

    public function post(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = [])
    {
        $this->addRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['POST']);
    }

    public function put(string $route, $actionCallback, array $parameters = [], array $requirements = [], array $options = [])
    {
        $this->addRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['PUT']);
    }

    public function patch($route, $actionCallback, $parameters = [], array $requirements = [], array $options = [])
    {
        $this->addRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['PATCH']);
    }

    public function delete($route, $actionCallback, $parameters = [], array $requirements = [], array $options = [])
    {
        $this->addRoute($route, $actionCallback, $parameters, $requirements, $options, '', [], ['DELETE']);
    }

    /** @param string|Closure $target */
    public function addRoute($target, $actionCallback, $defaults, array $requirements = [], array $options = [], ?string $host = '', $schemes = [], $methods = [], ?string $condition = '')
    {
        $name = $this->getNextRouteName();

        $routeClass = PathRoute::class;
        if (!is_string($target)) {
            $routeClass = CallbackRoute::class;
        }

        if ($defaults instanceof Closure) {
            $defaults = ['_parameterCallback' => $defaults];
        }

        $route = new $routeClass(
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

        $this->getRouteCollection()->add($route->getName(), $route);
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
            if (
                apply_filters('offbeatwp/route/match/wp', true, $callbackRoute) &&
                $callbackRoute->doMatchCallback()
            ) {
                return $callbackRoute;
            }
        }

        return false;
    }

    public function removeRoute($route)
    {
        if ($route instanceof Route) {
            $routeName = $route->getName();
        }

        if (
            $route instanceof Route &&
            $route->getOption('persistent') === true
        ) {
            return;
        }

        if (!empty($routeName)) {
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
