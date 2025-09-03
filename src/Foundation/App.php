<?php

namespace OffbeatWP\Foundation;

use Closure;
use DI\Container;
use DI\ContainerBuilder;
use DI\Definition\Helper\DefinitionHelper;
use InvalidArgumentException;
use OffbeatWP\Common\Singleton;
use OffbeatWP\Config\Config;
use OffbeatWP\Helpers\VarHelper;
use OffbeatWP\Services\AbstractService;

use function DI\autowire;

final class App extends Singleton
{
    /** @var array<non-falsy-string, AbstractService> */
    private array $services = [];
    public readonly Container $container;
    protected ?Config $config = null;

    public function bootstrap(): void
    {
        $containerBuilder = new ContainerBuilder();

        $this->initiateServices($containerBuilder);

        $this->container = $containerBuilder->build();

        $this->registerServices();
    }

    /** @param \DI\ContainerBuilder<Container> $containerBuilder */
    private function initiateServices(ContainerBuilder $containerBuilder): void
    {
        $services = config('services', false);

        if (is_array($services)) {
            foreach ($services as $service) {
                $this->initiateService($service, $containerBuilder);
            }
        }
    }

    /**
     * @param class-string<AbstractService> $serviceClass
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

            if (property_exists($service, 'bindings') && !empty($service->bindings) && is_array($service->bindings)) {
                foreach ($service->bindings as &$binding) {
                    if (is_string($binding)) {
                        $binding = autowire($binding);
                    }
                }
                $containerBuilder->addDefinitions($service->bindings);
            }

            $this->markServiceAsInitiated($service);
        } else {
            throw new InvalidArgumentException('Service "' . esc_html__($serviceClass) . '" could not be found.');
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

    public function getService(string $serviceClass): ?AbstractService
    {
        if ($this->isServiceInitiated($serviceClass)) {
            return $this->services[$serviceClass];
        }

        return null;
    }

    public function isServiceInitiated(string $serviceClass): bool
    {
        return (isset($this->services[$serviceClass]));
    }

    public function markServiceAsInitiated(AbstractService $service): void
    {
        $this->services[$service::class] = $service;
    }

    /** @param mixed|DefinitionHelper|Closure $concrete */
    public function bind(string $abstract, $concrete): void
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

    public function config(string $config, bool $collect = true): mixed
    {
        return $this->getConfigInstance()->get($config, $collect);
    }

    /** @return mixed[] */
    public function getConfigArray(string $config): array
    {
        return VarHelper::toArray($this->getConfigInstance()->get($config), []);
    }
}
