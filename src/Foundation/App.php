<?php

namespace OffbeatWP\Foundation;

use Closure;
use DI\Container;
use DI\ContainerBuilder;
use DI\Definition\Helper\CreateDefinitionHelper;
use DI\Definition\Helper\DefinitionHelper;
use OffbeatWP\Assets\AssetsManager;
use OffbeatWP\Assets\ServiceEnqueueScripts;
use OffbeatWP\Config\Config;
use OffbeatWP\Content\Post\Relations\PostRelationService;
use OffbeatWP\Helpers\VarHelper;
use OffbeatWP\Services\AbstractService;
use OffbeatWP\Wordpress\WordpressService;

use function DI\autowire;
use function DI\create;

final class App
{
    private static ?App $instance = null;

    /** @var AbstractService[] */
    private array $services = [];
    public Container $container;
    protected ?Config $config = null;

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
        $this->initiateBaseServices($containerBuilder);
        $this->initiateServices($containerBuilder);

        $this->container = $containerBuilder->build();

        $this->registerServices();
    }

    /** @return array{assets: CreateDefinitionHelper} */
    private function baseBindings(): array
    {
        return ['assets' => create(AssetsManager::class)];
    }

    /** @param \DI\ContainerBuilder<Container> $containerBuilder */
    private function initiateBaseServices(ContainerBuilder $containerBuilder): void
    {
        foreach ([WordpressService::class, ServiceEnqueueScripts::class, PostRelationService::class] as $service) {
            $this->initiateService($service, $containerBuilder);
        }
    }

    /** @param \DI\ContainerBuilder<Container> $containerBuilder */
    private function initiateServices(ContainerBuilder $containerBuilder): void
    {
        $services = config('services');

        if (is_array($services) && $services) {
            foreach ($services as $service) {
                $this->initiateService($service, $containerBuilder);
            }
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

    private function getConfigInstance(): Config
    {
        if ($this->config === null) {
            $this->config = new Config($this);
        }

        return $this->config;
    }

    /**
     * @param non-falsy-string $config
     * @return object|\Illuminate\Support\Collection|string|float|int|bool|null
     */
    public function config(string $config)
    {
        return $this->getConfigInstance()->get($config);
    }

    /** @return mixed[] */
    public function getConfigArray(string $config): array
    {
        return VarHelper::toArray($this->getConfigInstance()->get($config), []);
    }
}
