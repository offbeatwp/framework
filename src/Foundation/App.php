<?php

namespace OffbeatWP\Foundation;

use Closure;
use DI\Container;
use DI\ContainerBuilder;
use DI\Definition\Helper\CreateDefinitionHelper;
use DI\Definition\Helper\DefinitionHelper;
use OffbeatWP\Assets\AssetsManager;
use OffbeatWP\Assets\ServiceEnqueueScripts;
use OffbeatWP\Components\ComponentsService;
use OffbeatWP\Config\Config;
use OffbeatWP\Content\Post\Relations\Service;
use OffbeatWP\Exceptions\InvalidRouteException;
use OffbeatWP\Exceptions\WpErrorException;
use OffbeatWP\Helpers\VarHelper;
use OffbeatWP\Http\Http;
use OffbeatWP\Routes\Routes\CallbackRoute;
use OffbeatWP\Routes\Routes\PathRoute;
use OffbeatWP\Routes\Routes\Route;
use OffbeatWP\Routes\RoutesService;
use OffbeatWP\Services\AbstractService;
use OffbeatWP\Wordpress\WordpressService;
use WP_Error;

use function DI\autowire;
use function DI\create;

final class App
{
    private static ?App $instance = null;

    /** @var AbstractService[] */
    private array $services = [];
    public Container $container;
    protected ?Config $config = null;
    /** @var CallbackRoute|PathRoute|false|null */
    protected $route;

    public static function singleton(): App
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    private function __construct()
    {
        // App is a singleton and must instantiated via the App::singleton() method.
    }

    public function bootstrap(): void
    {
        $containerBuilder = new ContainerBuilder();

        $containerBuilder->addDefinitions($this->baseBindings());

        if (apply_filters('offbeatwp/initiate_base_services', true)) {
            $this->initiateBaseServices($containerBuilder);
        }

        $this->initiateServices($containerBuilder);

        $this->container = $containerBuilder->build();

        $this->registerServices();

        offbeat('hooks')->doAction('offbeat.ready');

        add_action('init', [$this, 'addRoutes'], PHP_INT_MAX - 1);
        add_action('wp', [$this, 'findRoute'], 1);
    }

    /** @return array{assets: CreateDefinitionHelper, http: CreateDefinitionHelper} */
    private function baseBindings(): array
    {
        return [
            'assets' => create(AssetsManager::class),
            'http' => create(Http::class),
        ];
    }

    /** @param \DI\ContainerBuilder<Container> $containerBuilder */
    private function initiateBaseServices(ContainerBuilder $containerBuilder): void
    {
        foreach ([WordpressService::class, RoutesService::class, ComponentsService::class, ServiceEnqueueScripts::class, Service::class] as $service) {
            $this->initiateService($service, $containerBuilder);
        }
    }

    /** @param \DI\ContainerBuilder<Container> $containerBuilder */
    private function initiateServices(ContainerBuilder $containerBuilder): void
    {
        $services = config('services');

        if (is_object($services) && $services->isNotEmpty()) {
            $services->each(function ($service) use ($containerBuilder) {
                $this->initiateService($service, $containerBuilder);
            });
        }
    }

    /**
     * @param string $serviceClass
     * @param \DI\ContainerBuilder<Container> $containerBuilder
     */
    private function initiateService(string $serviceClass, ContainerBuilder $containerBuilder): void
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
        } else {
            trigger_error('Class for service "' . $serviceClass . '" could not be found.');
        }
    }

    private function registerServices(): void
    {
        foreach ($this->services as $service) {
            if (is_callable([$service, 'register'])) {
                $this->container->call([$service, 'register']);
            }
        }
    }

    /** @return false|AbstractService */
    public function getService(string $serviceClass)
    {
        if ($this->isServiceInitiated($serviceClass)) {
            return $this->services[$serviceClass];
        }

        return false;
    }

    public function isServiceInitiated(string $serviceClass): bool
    {
        return (isset($this->services[$serviceClass]));
    }

    public function markServiceAsInitiated(object $service): void
    {
        $this->services[$service::class] = $service;
    }

    /**
     * @param string $abstract
     * @param mixed|DefinitionHelper|Closure $concrete
     * @return void
     */
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

    private function getConfigInstance(): Config
    {
        if ($this->config === null) {
            $this->config = new Config($this);
        }

        return $this->config;
    }

    /**
     * @param string|null $config
     * @return object|\Illuminate\Support\Collection|string|float|int|bool|null|Config
     */
    public function config(?string $config)
    {
        $instance = $this->getConfigInstance();
        return $config === null ? $instance : $instance->get($config);
    }

    /** @return mixed[] */
    public function getConfigArray(string $config): array
    {
        return VarHelper::toArray($this->getConfigInstance()->get($config), []);
    }

    public function addRoutes(): void
    {
        offbeat('routes')->addRoutes();
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

    /**
     * @param mixed[] $config
     * @return void|string
     * @throws WpErrorException
     */
    public function run($config = [])
    {
        $route = $this->route;

        try {
            // Remove route from collection so if there is a second run it skips this route
            if ($route) {
                offbeat('routes')->removeRoute($route);
            }

            $output = $this->runRoute($route);

            if ($output === false) {
                throw new InvalidRouteException('Route returned false, trying to find next match');
            }

            if ($output instanceof WP_Error) {
                throw new WpErrorException($output->get_error_message(), 404);
            }

            $output = apply_filters('route_render_output', $output); //Legacy
            $output = apply_filters('offbeatwp/route/output', $output);

            if (isset($config['return']) && $config['return'] === true) {
                return $output;
            }

            echo $output;
        } catch (InvalidRouteException) {
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

        trigger_error('No route matched on URI: ' . filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL));
        echo offbeat('http')->abort(404);
        exit;
    }
}
