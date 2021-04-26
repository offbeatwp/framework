<?php
namespace OffbeatWP\Routes;

use Closure;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use OffbeatWP\Routes\RouteCollection;

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
     * @var string|Callable $target
     */
    public function addRoute($target, $actionCallback, $defaults, array $requirements = [], array $options = [],  ? string $host = '', $schemes = [], $methods = [],  ? string $condition = '')
    {
        if ($defaults instanceof Closure) {
            $defaults = ['_parameterCallback' => $defaults];
        }

        $route = new Route(
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

        $this->getRouteCollection()->add($this->getNextRouteName(), $route);
    }

    public function findUrlMatch()
    {
        // $request = Request::createFromGlobals(); // Disabled, gave issues with uploads
        $request = Request::create($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $_REQUEST, $_COOKIE, [], $_SERVER);

        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->getRouteCollection()->findByType('path'), $context);

        try {
            $parameters = $matcher->match($request->getPathInfo());

            if (!apply_filters('offbeatwp/route/match/url', true, $matcher)) {
                throw new Exception('Route not match (override)');
            }

            $this->lastMatchRoute = $parameters['_route'];

            $route =  $this->getRouteCollection()->get($parameters['_route']);
            $route->addDefaults($parameters);
            
            return $route;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function findMatch($dryCheck = false)
    {
        $callbackRoutes = $this->getRouteCollection()->findByType('callback');

        /**
         * @var Route $callbackRoute
         */
        foreach ($callbackRoutes->all() as $callbackRouteName => $callbackRoute) {
            
            if (
                apply_filters('offbeatwp/route/match/wp', true, $callbackRoute) && 
                $callbackRoute->doMatchCallback()
            ) {
                if (!$dryCheck) {
                    // Forget this "route". When a findMatch is performed again later in the process it prevents an endless loop.
                    $this->getRouteCollection()->remove($callbackRouteName);
                }

                return $callbackRoute;
            }

        }
        return false;
    }

    public function removeLastMatchRoute()
    {
        if (isset($this->lastMatchRoute) && !empty($this->lastMatchRoute)) {
            $this->getRouteCollection()->remove($this->lastMatchRoute);
        }
    }

    public function getNextRouteName()
    {
        $routeName = 'route' . $this->routeIterator;
        $this->routeIterator++;

        return $routeName;
    }
}
