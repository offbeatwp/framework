<?php
namespace OffbeatWP\Foundation;

use DI\ContainerBuilder;
use Exception;
use OffbeatWP\Assets\AssetsManager;
use OffbeatWP\Assets\ServiceEnqueueScripts;
use OffbeatWP\Components\ComponentsService;
use OffbeatWP\Config\Config;
use OffbeatWP\Content\Post\Relations\Service;
use OffbeatWP\Exceptions\InvalidRouteException;
use OffbeatWP\Exceptions\WpErrorException;
use OffbeatWP\Http\Http;
use OffbeatWP\Routes\Routes\Route;
use OffbeatWP\Routes\RoutesService;
use OffbeatWP\Wordpress\WordpressService;
use WP_Error;
use function DI\autowire;
use function DI\create;

class App
{
    private static $instance;
    public $container;
    private $services = [];
    protected $config = null;
    protected $route;

    public static function singleton()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /** @throws Exception */
    public function bootstrap(): void
    {
        $containerBuilder = new ContainerBuilder();

        $containerBuilder->addDefinitions($this->baseBindings());
        $this->initiateBaseServices($containerBuilder);
        $this->initiateServices($containerBuilder);

        $this->container = $containerBuilder->build();

        $this->registerServices();

        offbeat('hooks')->doAction('offbeat.ready');

        add_filter('wp', [$this, 'findRoute'], 0);
    }

    private function baseBindings(): array
    {
        return [
            'assets' => create(AssetsManager::class),
            'http' => create(Http::class),
        ];
    }

    private function initiateBaseServices($containerBuilder): void
    {
        foreach ([WordpressService::class, RoutesService::class, ComponentsService::class, ServiceEnqueueScripts::class, Service::class] as $service) {
            $this->initiateService($service, $containerBuilder);
        }
    }

    private function initiateServices($containerBuilder): void
    {
        $services = config('services');

        if (is_object($services) && $services->isNotEmpty()) {
            $services->each(function ($service) use ($containerBuilder) {
                $this->initiateService($service, $containerBuilder);
            });
        }
    }

    private function initiateService($serviceClass, $containerBuilder): void
    {
        if ($this->isServiceInitiated($serviceClass)) {
            $this->getService($serviceClass);
            return;
        }

        if (class_exists($serviceClass)) {
            $service = new $serviceClass($this);

            if (property_exists($service, 'bindings') && !empty($service->bindings)) {
                foreach ($service->bindings as &$binding) {
                    if (is_string($binding)) {
                        $binding = autowire($binding);
                    }
                }
                $containerBuilder->addDefinitions($service->bindings);
            }

            $this->markServiceAsInitiated($service);
        }
    }

    private function registerServices(): void
    {
        if ($this->services) {
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

    public function isServiceInitiated($serviceClass): bool
    {
        return (isset($this->services[$serviceClass]));
    }

    public function markServiceAsInitiated($service): void
    {
        $this->services[get_class($service)] = $service;
    }

    public function bind($abstract, $concrete)
    {
        $this->container->set($abstract, $concrete);
    }

    public function configPath(): string
    {
        return get_template_directory() . '/config';
    }

    public function routesPath(): string
    {
        return get_template_directory() . '/routes';
    }

    public function componentsPath(): string
    {
        return get_template_directory() . '/components';
    }

    public function config($config, $default)
    {
        if ($this->config === null) {
            $this->config = new Config($this);
        }

        if ($config !== null) {
            return $this->config->get($config, $default);
        }
        return $this->config;
    }

    public function findRoute(): void
    {
        if (current_action() === 'wp' && is_admin()) {
            return;
        }

        do_action('offbeatwp/route/match/before');

        $route = offbeat('routes')->findRoute();

        do_action('offbeatwp/route/match/after', $route);

        $this->route = $route;
    }

    /** @throws WpErrorException */
    public function run($config = []): void
    {
        $route = $this->route;

        try {
            // Remove route from collection so if there is a second run it skips this route
            offbeat('routes')->removeRoute($route);

            $output = $this->runRoute($route);

            if ($output === false) {
                throw new InvalidRouteException('Route returned false, trying to find next match');
            }

            if ($output instanceof WP_Error) {
                throw new WpErrorException($output->get_error_message());
            }

            $output = apply_filters('route_render_output', $output); //Legacy
            $output = apply_filters('offbeatwp/route/output', $output);

            echo $output;
        } catch (InvalidRouteException $e) {
            // Find new route
            $this->findRoute();
            $this->run($config);
        }
    }

    /**
     * @param Route|false $route
     * @return false|string|WP_Error
     */
    public function runRoute($route)
    {
        $route = apply_filters('offbeatwp/route/run/init', $route);

        if ($route !== false && $route->hasValidActionCallback()) {
            $actionReturn = apply_filters('offbeatwp/route/run/pre', false, $route);

            if (!$actionReturn) {
                $route = $route->runMiddleware();
                $actionReturn = $route->doActionCallback();
            }

            $actionReturn = apply_filters('offbeatwp/route/run/post', $actionReturn, $route);

            if (is_array($actionReturn)) {
                header('Content-type: application/json');
                return json_encode($actionReturn);
            }

            return $actionReturn;
        }

        return new WP_Error('broke', sprintf(__('No route matched on URI: %s', 'offbeatwp'), $_SERVER['REQUEST_URI'] ?? '???'));
    }
}
