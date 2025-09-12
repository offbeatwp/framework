<?php

namespace OffbeatWP\Foundation;

use InvalidArgumentException;
use OffbeatWP\Config\Config;
use OffbeatWP\Content\Common\Singleton;
use OffbeatWP\Contracts\View;
use OffbeatWP\Helpers\VarHelper;
use OffbeatWP\Services\AbstractService;

final class App extends Singleton
{
    /** @var array<non-falsy-string, AbstractService> */
    private array $services = [];
    private ?Config $config = null;
    public ?View $viewRenderer = null;

    public function bootstrap(): void
    {
        $this->initiateServices();
        $this->registerServices();
    }

    private function initiateServices(): void
    {
        $services = config('services', false);

        if (is_array($services)) {
            /** @var class-string<AbstractService> $service */
            foreach ($services as $service) {
                $this->initiateService($service);
            }
        }
    }

    /** @param class-string<AbstractService> $serviceClass */
    private function initiateService(string $serviceClass): void
    {
        if (!class_exists($serviceClass)) {
            throw new InvalidArgumentException('Service "' . esc_html__($serviceClass) . '" could not be found.');
        }

        $this->services[$serviceClass] = new $serviceClass($this);
    }

    private function registerServices(): void
    {
        foreach ($this->services as $service) {
            $service->register();
        }
    }

    public function getService(string $serviceClass): ?AbstractService
    {
        if (array_key_exists($serviceClass, $this->services)) {
            return $this->services[$serviceClass];
        }

        return null;
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
