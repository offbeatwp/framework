<?php
namespace OffbeatWP\Foundation;

use DI\Container;
use DI\ContainerBuilder;
use OffbeatWP\Assets\AssetsManager;
use OffbeatWP\Assets\ServiceEnqueueScripts;
use OffbeatWP\Components\ComponentsService;
use OffbeatWP\Config\Config;
use OffbeatWP\Content\Post\Relations\Service;
use OffbeatWP\Services\AbstractService;
use OffbeatWP\Wordpress\WordpressService;
use RuntimeException;
use function DI\autowire;
use function DI\create;

final class App
{
    private static ?App $instance = null;

    /** @var array<class-string<AbstractService>, AbstractService> */
    private array $services = [];
    private ?Config $config = null;
    private Container $container;

    private function __construct()
    {
        // App is a singleton and must instantiated via the App::singleton() method.
    }

    public static function singleton(): App
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function bootstrap(): void
    {
        $containerBuilder = new ContainerBuilder();

        // Base Bindings
        $containerBuilder->addDefinitions([AssetsManager::class => create(AssetsManager::class)]);

        // Initiate Base Services
        foreach ([WordpressService::class, ComponentsService::class, ServiceEnqueueScripts::class, Service::class] as $service) {
            $this->initiateService($service, $containerBuilder);
        }

        // Initiate Config Services
        foreach ($this->config('services') as $service) {
            $this->initiateService($service, $containerBuilder);
        }

        // Build container and register services
        $this->container = $containerBuilder->build();

        foreach ($this->services as $service) {
            $service->register();
            $this->container->call([$service, 'register']);
        }

        do_action_ref_array('offbeat.ready', []);
    }

    /**
     * @param class-string<AbstractService> $serviceClass
     * @param \DI\ContainerBuilder<Container> $containerBuilder
     */
    private function initiateService(string $serviceClass, ContainerBuilder $containerBuilder): void
    {
        if (array_key_exists($serviceClass, $this->services)) {
            throw new RuntimeException($serviceClass . ' was initiated twice.');
        }

        if (class_exists($serviceClass)) {
            $service = new $serviceClass($this);

            if ($service->bindings) {
                foreach ($service->bindings as &$binding) {
                    if (is_string($binding)) {
                        $binding = autowire($binding);
                    } else {
                        throw new RuntimeException('Cannot autowire binding with type ' . gettype($binding));
                    }
                }

                unset($binding);
                $containerBuilder->addDefinitions($service->bindings);
            }

            $this->services[$service::class] = $service;
        } else {
            throw new RuntimeException('Service class ' . $serviceClass . ' does not exist!');
        }
    }

    public function getService(string $serviceClass): AbstractService
    {
        if (array_key_exists($serviceClass, $this->services)) {
            return $this->services[$serviceClass];
        }

        throw new RuntimeException('Cannot get service ' . $serviceClass . ' before initialisation.');
    }

    public function bind(string $abstract, mixed $concrete): void
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

    public function config(string $config): mixed
    {
        if ($this->config === null) {
            $this->config = new Config($this);
        }

        if ($config) {
            return $this->config->get($config);
        }

        return $this->config;
    }
}
