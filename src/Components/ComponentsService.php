<?php

namespace OffbeatWP\Components;

use OffbeatWP\Services\AbstractService;
use ReflectionClass;

class ComponentsService extends AbstractService
{
    /** @var array{components: class-string<\OffbeatWP\Components\ComponentRepository>} */
    public array $bindings = [
        'components' => ComponentRepository::class
    ];

    /** @return void */
    public function register()
    {
        offbeat('hooks')->addAction('offbeat.ready', [$this, 'registerComponents']);
    }

    public function registerComponents(): void
    {
        $components = $this->registrableComponents();

        if ($components) {
            /** @var class-string<\OffbeatWP\Components\AbstractComponent> $class */
            foreach ($components as $class) {
                container('components')->register($class::getSlug(), $class);
            }
        }
    }

    /**
     * @return class-string<\OffbeatWP\Components\AbstractComponent>[]|null
     * @throws \ReflectionException
     */
    public function registrableComponents(): ?array
    {
        $activeComponents = [];
        $componentsDirectory = $this->getComponentsDirectory();

        if (!is_dir($componentsDirectory)) {
            return null;
        }

        $handle = opendir($componentsDirectory);
        if ($handle) {
            while (($entry = readdir($handle)) !== false) {
                if (is_dir($componentsDirectory . '/' . $entry) && !preg_match('/^(_|\.)/', $entry)) {
                    $activeComponents[] = $entry;
                }
            }

            closedir($handle);
        }

        $components = [];

        foreach ($activeComponents as $activeComponent) {
            /** @var class-string<\OffbeatWP\Components\AbstractComponent> $compomentClass */
            $compomentClass = "Components\\" . $activeComponent . "\\" . $activeComponent;
            $compomentReflectionClass = new ReflectionClass($compomentClass);

            if (!$compomentReflectionClass->isAbstract()) {
                $components[$activeComponent] = $compomentClass;
            }
        }

        return array_unique($components);
    }

    public function getComponentsDirectory(): string
    {
        return $this->app->componentsPath();
    }
}
