<?php
namespace OffbeatWP\Components;

use OffbeatWP\Services\AbstractService;
use ReflectionClass;

class ComponentsService extends AbstractService
{
    public $bindings = [
        'components' => ComponentRepository::class
    ];

    public function register()
    {
        offbeat('hooks')->addAction('offbeat.ready', [$this, 'registerComponents']);
    }

    public function registerComponents()
    {
        $components = $this->registrableComponents();

        if ($components) {
            foreach ($components as $class) {
                container('components')->register($class::getSlug(), $class);
            }
        }
    }

    public function registrableComponents()
    {
        $activeComponents = [];
        $componentsDirectory = $this->getComponentsDirectory();

        if (!is_dir($componentsDirectory)) {
            return null;
        }

        if ($handle = opendir($componentsDirectory)) {
            while (($entry = readdir($handle)) !== false) {
                if (!is_dir($componentsDirectory . '/' . $entry) || preg_match('/^(_|\.)/', $entry)) {
                    continue;
                }

                $activeComponents[] = $entry;
            }

            closedir($handle);
        }

        $components = [];

        foreach ($activeComponents as $activeComponent) {
            $compomentClass = "Components\\" . $activeComponent . "\\" . $activeComponent;
            $compomentReflectionClass = new ReflectionClass($compomentClass);

            if ($compomentReflectionClass->isAbstract()) {
                continue;
            }

            $components[$activeComponent] = $compomentClass;
        }

        return array_unique($components);
    }

    public function getComponentsDirectory()
    {
        return $this->app->componentsPath();
    }
}
