<?php
namespace OffbeatWP\Foundation;

use DI\Container;
use DI\ContainerBuilder;
use DI\Definition\Helper\CreateDefinitionHelper;
use ErrorException;
use OffbeatWP\Assets\AssetsManager;
use OffbeatWP\Assets\ServiceEnqueueScripts;
use OffbeatWP\Components\ComponentsService;
use OffbeatWP\Config\Config;
use OffbeatWP\Content\Post\Relations\Service;
use OffbeatWP\Services\AbstractService;
use OffbeatWP\Wordpress\WordpressService;
use function DI\autowire;
use function DI\create;

final class App
{
    private static ?App $instance = null;

    /** @var AbstractService[] */
    private array $services = [];
    private ?Config $config = null;
    public readonly Container $container;

    private function __construct(Container $container) {
        $this->container = $container;
    }

    public static function singleton(): App
    {
        if (!static::$instance) {
            throw new ErrorException('Offbeat has not been bootstrapped.');
        }

        return static::$instance;
    }

    public function bootstrap(): void
    {
        $containerBuilder = new ContainerBuilder();

        $containerBuilder->addDefinitions($this->baseBindings());
        $this->initiateBaseServices($containerBuilder);
        $this->initiateServices($containerBuilder);

        $container = $containerBuilder->build();

        foreach ($this->services as $service) {
            $container->call([$service, 'register']);
        }

        do_action('offbeat.ready');

        self::$instance = new static($container);
    }

    /** @return CreateDefinitionHelper[] */
    private function baseBindings(): array
    {
        return [AssetsManager::class => create(AssetsManager::class)];
    }

    private function initiateBaseServices(ContainerBuilder $containerBuilder): void
    {
        foreach ([WordpressService::class, ComponentsService::class, ServiceEnqueueScripts::class, Service::class] as $service) {
            $this->initiateService($service, $containerBuilder);
        }
    }

    private function initiateServices(ContainerBuilder $containerBuilder): void
    {
        $services = config('services');

        if (is_object($services) && $services->isNotEmpty()) {
            $services->each(function ($service) use ($containerBuilder) {
                $this->initiateService($service, $containerBuilder);
            });
        }
    }

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
        }
    }

    /** @return null|false|AbstractService */
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
