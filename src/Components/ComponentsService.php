<?php
namespace OffbeatWP\Components;

use OffbeatWP\Contracts\SiteSettings;
use OffbeatWP\Contracts\View;
use OffbeatWP\Services\AbstractService;
use ReflectionClass;

final class ComponentsService extends AbstractService
{
    public array $bindings = [ComponentRepository::class => ComponentRepository::class];

    public function register(SiteSettings $settings, View $view): void
    {
        add_action('offbeat.ready', [$this, 'registerComponents']);
    }

    public function registerComponents(): void
    {
        foreach ($this->registrableComponents() as $class) {
            offbeat(ComponentRepository::class)->register($class::getSlug(), $class);
        }
    }

    /**
     * @return array<string, string>
     * @throws \ReflectionException
     */
    public function registrableComponents(): array
    {
        /** @var string[] $activeComponents */
        $activeComponents = [];
        $componentsDirectory = $this->getComponentsDirectory();

        if (!is_dir($componentsDirectory)) {
            return [];
        }

        $handle = opendir($componentsDirectory);
        if ($handle) {
            $entry = readdir($handle);
            while ($entry !== false) {
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

    public function getComponentsDirectory(): string
    {
        return $this->app->componentsPath();
    }
}
