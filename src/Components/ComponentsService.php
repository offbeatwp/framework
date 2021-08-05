<?php
namespace OffbeatWP\Components;

use OffbeatWP\Services\AbstractService;

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

        if (!empty($components)) {
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
            while (false !== ($entry = readdir($handle))) {
                if (!is_dir($componentsDirectory . '/' . $entry) || preg_match('/^(_|\.)/', $entry)) {
                    continue;
                }

                $activeComponents[] = $entry;
            }

            closedir($handle);
        }

        $components = [];

        foreach ($activeComponents as $activeComponent) {
            $components[$activeComponent] = "Components\\" . $activeComponent . "\\" . $activeComponent;
        }

        return array_unique($components);
    }

    public function getComponentsDirectory()
    {
        return $this->app->componentsPath();
    }
}