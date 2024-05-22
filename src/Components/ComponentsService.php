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
            $compomentClass = "Components\\" . $activeComponent . "\\" . $activeComponent;
            $compomentReflectionClass = new ReflectionClass($compomentClass);

            if (!$compomentReflectionClass->isAbstract()) {
                $components[$activeComponent] = $compomentClass;
            }
        }

        return array_unique($components);
    }

    public function getComponentsDirectory()
    {
        return $this->app->componentsPath();
    }
}
