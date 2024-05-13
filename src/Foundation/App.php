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
use OffbeatWP\Http\Http;
use OffbeatWP\Routes\Routes\CallbackRoute;
use OffbeatWP\Routes\Routes\PathRoute;
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
    /** @var CallbackRoute|PathRoute|false|null */
    protected $route;

    public static function singleton(): App
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    private function __construct() {
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

        offbeat('hooks')->doAction('offbeat.ready');
    }

    /** @return CreateDefinitionHelper[] */
    private function baseBindings(): array
    {
        return [
            'assets' => create(AssetsManager::class),
            'http' => create(Http::class),
        ];
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
        $this->services[get_class($service)] = $service;
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

    /**
     * @param string|null $config
     * @param null $default
     * @return object|\Illuminate\Support\Collection|string|float|int|bool|null|Config
     */
    public function config(?string $config, $default)
    {
        if ($this->config === null) {
            $this->config = new Config($this);
        }

        if ($config !== null) {
            return $this->config->get($config, $default);
        }

        return $this->config;
    }
}
