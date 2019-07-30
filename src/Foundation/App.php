<?php
namespace OffbeatWP\Foundation;

use DI\ContainerBuilder;
use OffbeatWP\Config\Config;

class App
{
    private static $instance;
    public $container;
    private $services = [];
    protected $config = null;

    public static function singleton()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    public function bootstrap()
    {
        // add_filter('deprecated_file_trigger_error', '__return_false');

        $containerBuilder = new ContainerBuilder();

        $containerBuilder->addDefinitions($this->baseBindings());
        $this->initiateBaseServices($containerBuilder);
        $this->initiateServices($containerBuilder);

        $this->container = $containerBuilder->build();

        $this->registerServices();

        offbeat('hooks')->doAction('offbeat.ready');
    }

    private function baseBindings()
    {
        return [
            'assets' => \DI\create(\OffbeatWP\Assets\AssetsManager::class),
            'http' => \DI\create(\OffbeatWP\Http\Http::class),
        ];
    }

    private function initiateBaseServices($containerBuilder)
    {
        foreach ([
            \OffbeatWP\Wordpress\WordpressService::class,
            \OffbeatWP\Routes\RoutesService::class,
            \OffbeatWP\Components\ComponentsService::class,
            \OffbeatWP\Assets\ServiceEnqueueScripts::class,
            OffbeatWP\Content\Post\Relations\Service::class,
        ] as $service) {
            $this->initiateService($service, $containerBuilder);
        }
    }

    private function initiateServices($containerBuilder)
    {
        $services = config('services');

        if (is_object($services) && $services->isNotEmpty()) {
            $services->each(function ($service) use ($containerBuilder) {
                $this->initiateService($service, $containerBuilder);
            });
        }
    }

    private function initiateService($serviceClass, $containerBuilder)
    {
        if ($this->isServiceInitiated($serviceClass)) {
            return $this->getService($serviceClass);
        }

        if (class_exists($serviceClass)) {
            $service = new $serviceClass($this);

            if (property_exists($service, 'bindings') && !empty($service->bindings)) {
                foreach ($service->bindings as &$binding) {
                    if (is_string($binding)) {
                        $binding = \DI\autowire($binding);
                    }
                }
                $containerBuilder->addDefinitions($service->bindings);
            }

            $this->markServiceAsInitiated($service);
        }

    }

    private function registerServices()
    {
        if (!empty($this->services)) {
            foreach ($this->services as $service) {
                if (is_callable([$service, 'register'])) {
                    $this->container->call([$service, 'register']);
                }
            }
        }

    }

    public function getService($serviceClass)
    {
        if ($this->isServiceInitiated($serviceClass)) {
            return $this->services[$serviceClass];
        }

        return false;
    }

    public function isServiceInitiated($serviceClass)
    {
        if (isset($this->services[$serviceClass])) {
            return true;
        }

        return false;
    }

    public function markServiceAsInitiated($service)
    {
        $this->services[get_class($service)] = $service;
    }

    public function bind($abstract, $concrete)
    {
        $this->container->set($abstract, $concrete);
    }

    public function configPath()
    {
        return get_template_directory() . '/config';
    }

    public function routesPath()
    {
        return get_template_directory() . '/routes';
    }

    public function componentsPath()
    {
        return get_template_directory() . '/components';
    }

    public function config($config, $default)
    {
        if (is_null($this->config)) {
            $this->config = new Config($this);
        }

        if (!is_null($config)) {
            return $this->config->get($config, $default);
        }
        return $this->config;
    }

    public function run($config = [])
    {
        do_action('before_route_matching');

        $route = offbeat('routes')->findUrlMatch();
        
        if (!$route) {
            $route = offbeat('routes')->findMatch();
        }

        echo  $this->runRoute($route);
    }

    public function runRoute($route)
    {
        $route = apply_filters('offbeatwp/route/run', $route);

        if ($route !== false && is_callable($route['actionCallback'])) {
            $parameters = $route['parameters'];

            if ($parameters instanceof \Closure) {
                $parameters = $route['parameters']();
            }

            $actionReturn = container()->call($route['actionCallback'], $parameters);

            if (is_array($actionReturn)) {
                header('Content-type: application/json');
                return json_encode($actionReturn);
            } else {
                return $actionReturn;
            }

            return;
        }

        return new \WP_Error('broke', __("No route matched", 'raow'));
    }

}
