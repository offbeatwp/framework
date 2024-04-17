<?php

namespace OffbeatWP\Modules;

use OffbeatWP\Components\ComponentRepository;
use OffbeatWP\Foundation\App;
use OffbeatWP\Services\AbstractService;
use OffbeatWP\Support\Wordpress\Hooks;
use ReflectionClass;
use RuntimeException;

abstract class AbstractModule extends AbstractService
{
    public function __construct(App $app)
    {
        parent::__construct($app);

        if (method_exists($this, 'boot')) {
            offbeat(Hooks::class)->addAction('raow.ready', [$this, 'boot']);
        }

        add_action('init', function () {
            $this->registerComponents();
        }, 0);

        if (method_exists($this, 'wpInit')) {
            add_action('init', [$this, 'wpInit']);
        }

        if (is_admin() && method_exists($this, 'wpInitAdmin')) {
            add_action('init', [$this, 'wpInitAdmin']);
        }
    }

    public function getName(): string
    {
        return (new ReflectionClass($this))->getShortName();
    }

    public function registerComponents(): void
    {
        $directory = $this->getDirectory() . '/Components';

        $registerableComponents = $this->getRegisterableObjects($directory);

        if ($registerableComponents) {
            foreach ($registerableComponents as $class) {
                offbeat(ComponentRepository::class)->register($class::getSlug(), $class);
            }
        }
    }

    public function getNamespace(): string
    {
        $classInfo = new ReflectionClass($this);
        return substr($classInfo->name, 0, strrpos($classInfo->name, "\\"));
    }

    public function getDirectory(): string
    {
        $classInfo = new ReflectionClass($this);
        $classPath = $classInfo->getFileName();

        return dirname($classPath);
    }

    public function getUrl(): string
    {
        return get_stylesheet_directory_uri() . str_replace(get_stylesheet_directory(), '', $this->getDirectory());
    }

    private function getRegisterableObjects(string $path): array
    {
        if (!is_dir($path)) {
            return throw new RuntimeException($path . ' is not a directory.');
        }

        $paths = array_filter(glob($path . '/*', GLOB_ONLYDIR), fn($path) => !str_starts_with(basename($path), '_'));
        $objects = [];

        foreach ($paths as $p) {
            $baseName = basename($p);
            $objects[$baseName] = $this->getNamespace() . "\Components\\" . $baseName . "\\" . $baseName;
        }

        return $objects;
    }
}
