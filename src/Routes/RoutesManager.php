<?php
namespace OffbeatWP\Routes;

use Closure;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use OffbeatWP\Routes\RouteCollection;
use OffbeatWP\Routes\Routes\CallbackRoute;
use OffbeatWP\Routes\Routes\PathRoute;
use OffbeatWP\Routes\Routes\Route;

class RoutesManager
{
    protected $actions;
    protected $routeCollection;
    protected $routesContext;
    protected $routeIterator = 0;

    public function __construct()
    {
        $this->routeCollection = new RouteCollection();
        $this->actions = collect();
    }

    public function getRouteCollection():RouteCollection {
        return $this->routeCollection;
    }

    /**
     * Callbacks are executed in LiFo order.
     *
     * @param $checkCallback
     * @param $actionCallback
     * @param array $parameters
     */
    public function callback($checkCallback, $actionCallback, $parameters = [], $settings = [])
    {
        $this->addRoute($checkCallback, $actionCallback, $parameters);
    }

    public function get($route, $actionCallback, $parameters = [], $requirements = [])
    {
        $this->addRoute($route, $actionCallback, $parameters, $requirements, [], '', [], ['GET']);
    }

    public function post($route, $parameters = [], $requirements = [])
    {
        $this->addRoute($route, $actionCallback, $parameters, $requirements, [], '', [], ['POST']);
    }

    public function put($route, $parameters = [], $requirements = [])
    {
        $this->addRoute($route, $actionCallback, $parameters, $requirements, [], '', [], ['PUT']);
    }

    public function patch($route, $actionCallback, $parameters = [], $requirements = [])
    {
        $this->addRoute($route, $actionCallback, $parameters, $requirements, [], '', [], ['PATCH']);
    }

    public function delete($route, $actionCallback, $parameters = [], $requirements = [])
    {
        $this->addRoute($route, $actionCallback, $parameters, $requirements, [], '', [], ['DELETE']);
    }

    /**
     * @var string|Closure $target
     */
    public function addRoute($target, $actionCallback, $defaults, array $requirements = [], array $options = [],  ? string $host = '', $schemes = [], $methods = [],  ? string $condition = '')
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

    public function findMatch() {
        $route = $this->findPathMatch();

        if (!$route) {
            $route = $this->findCallbackMatch();
        }

        return $route;
    }

    public function findPathMatch()
    {
        // $request = Request::createFromGlobals(); // Disabled, gave issues with uploads
        $request = Request::create($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $_REQUEST, $_COOKIE, [], $_SERVER);

        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->getRouteCollection()->findByType(PathRoute::class), $context);

        try {
            $parameters = $matcher->match($request->getPathInfo());

            if (!apply_filters('offbeatwp/route/match/url', true, $matcher)) {
                throw new Exception('Route not match (override)');
            }

            $route =  $this->getRouteCollection()->get($parameters['_route']);
            $route->addDefaults($parameters);
            
            return $route;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function findCallbackMatch()
    {
        $callbackRoutes = $this->getRouteCollection()->findByType(CallbackRoute::class);

        /**
         * @var CallbackRoute $callbackRoute
         */
        foreach ($callbackRoutes->all() as $callbackRouteName => $callbackRoute) {
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
            $route = $route->getName();
        }

        if (!empty($route)) {
            $this->getRouteCollection()->remove($route);
        }
    }

    public function getNextRouteName()
    {
        $routeName = 'route' . $this->routeIterator;
        $this->routeIterator++;

        return $routeName;
    }
}
