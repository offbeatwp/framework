<?php
namespace OffbeatWP\Routes;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RoutesManager
{
    protected $actions;
    protected $routesCollection;
    protected $routesContext;
    protected $routeIterator = 0;
    protected $lastMatchRoute;

    public function __construct()
    {
        $this->routesCollection = new RouteCollection();
        $this->actions = collect();
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
        $action = [
            'actionCallback' => $actionCallback,
            'checkCallback'  => $checkCallback,
            'parameters'     => $parameters,
            'isWpCallback'   => (isset($settings['isWpCallback']) && is_bool($settings['isWpCallback'])) ? $settings['isWpCallback'] : true
        ];

        $this->actions->push($action);
    }

    public function get($route, $actionCallback, $parameters = [], $requirements = [])
    {
        $parameters['_callback'] = $actionCallback;
        $this->addRoute($route, $parameters, $requirements, [], '', [], ['GET']);
    }

    public function post($route, $actionCallback, $parameters = [], $requirements = [])
    {
        $parameters['_callback'] = $actionCallback;
        $this->addRoute($route, $parameters, $requirements, [], '', [], ['POST']);
    }

    public function put($route, $actionCallback, $parameters = [], $requirements = [])
    {
        $parameters['_callback'] = $actionCallback;
        $this->addRoute($route, $parameters, $requirements, [], '', [], ['PUT']);
    }

    public function patch($route, $actionCallback, $parameters = [], $requirements = [])
    {
        $parameters['_callback'] = $actionCallback;
        $this->addRoute($route, $parameters, $requirements, [], '', [], ['PATCH']);
    }

    public function delete($route, $actionCallback, $parameters = [], $requirements = [])
    {
        $parameters['_callback'] = $actionCallback;
        $this->addRoute($route, $parameters, $requirements, [], '', [], ['DELETE']);
    }

    public function addRoute(string $path, array $defaults = [], array $requirements = [], array $options = [],  ? string $host = '', $schemes = [], $methods = [],  ? string $condition = '')
    {
        $route = new Route(
            $path, // path
            $defaults, // default values
            $requirements, // requirements
            $options, // options
            $host, // host
            $schemes, // schemes
            $methods, // methods
            $condition // condition
        );

        $this->routesCollection->add($this->getNextRouteName(), $route);
    }

    public function findUrlMatch()
    {
        // $request = Request::createFromGlobals(); // Disabled, gave issues with uploads
        $request = Request::create($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], $_REQUEST, $_COOKIE, [], $_SERVER);

        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->routesCollection, $context);

        try {
            $parameters = $matcher->match($request->getPathInfo());

            if (!apply_filters('offbeatwp/route/match/url', true, $matcher)) {
                throw new Exception('Route not match (override)');
            }

            $this->lastMatchRoute = $parameters['_route'];

            return [
                'actionCallback' => $parameters['_callback'],
                'parameters'     => $parameters,
            ];
        } catch (\Exception $e) {
            return false;
        }
    }

    public function findMatch($dryCheck = false)
    {
        $actions = $this->actions;

        if ($dryCheck) {
            $actions = $actions->where('isWpCallback', false);
        }

        foreach ($actions as $actionKey => $action) {
            if (
                apply_filters('offbeatwp/route/match/wp', true, $action) && 
                $action['checkCallback']()
            ) {
                if (!$dryCheck && !$action['isWpCallback']) {
                    // Forget this "route". When a findMatch is performed again later in the process it prevents an endless loop.
                    $actions->forget($actionKey);
                }

                return $action;
            }

        }

        return false;
    }

    public function removeLastMatchRoute()
    {
        if (isset($this->lastMatchRoute) && !empty($this->lastMatchRoute)) {
            $this->routesCollection->remove($this->lastMatchRoute);
        }
    }

    public function getNextRouteName()
    {
        $routeName = 'route' . $this->routeIterator;
        $this->routeIterator++;

        return $routeName;
    }
}
