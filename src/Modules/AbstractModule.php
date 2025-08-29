<?php

namespace OffbeatWP\Modules;

use Illuminate\Support\Collection;
use OffbeatWP\Foundation\App;
use OffbeatWP\Services\AbstractService;
use ReflectionClass;

abstract class AbstractModule extends AbstractService
{
    /** @var App */
    public $app;

    /** @param App $app */
    public function __construct($app)
    {
        $this->app = $app;
        parent::__construct($app);

        if (method_exists($this, 'boot')) {
            add_action('raow.ready', [$this, 'boot']);
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

    /** @return string */
    public function getName()
    {
        return (new ReflectionClass($this))->getShortName();
    }

    /** @return void */
    public function registerComponents()
    {
        $directory = $this->getDirectory() . '/Components';

        $registerableComponents = $this->getRegisterableObjects($directory, true);

        if ($registerableComponents) {
            foreach ($registerableComponents as $class) {
                offbeat('components')->register($class::getSlug(), $class);
            }
        }
    }

    /** @return string */
    public function getNamespace()
    {
        $classInfo = new ReflectionClass($this);
        return substr($classInfo->name, 0, strrpos($classInfo->name, "\\"));
    }

    /** @return string */
    public function getDirectory()
    {
        $classInfo = new ReflectionClass($this);
        $classPath = $classInfo->getFileName();

        return dirname($classPath);
    }

    /** @return string */
    public function getUrl()
    {
        $path = str_replace(get_stylesheet_directory(), '', $this->getDirectory());

        return get_stylesheet_directory_uri() . $path;
    }

    /**
     * @param string $path
     * @param bool $findDirs This parameter is unused and does not do unless your module overrides this method.
     * @return Collection<string, string>|null
     */
    public function getRegisterableObjects($path, $findDirs = false)
    {
        if (!is_dir($path)) {
            return null;
        }

        $paths = glob($path . '/*', GLOB_ONLYDIR);
        $objects = collect($paths)->filter(function ($path) {
            return !str_starts_with(basename($path), '_');
        })->mapWithKeys(function ($path) {
            $baseName = basename($path);

            return [$baseName => $this->getNamespace() . "\Components\\" . $baseName . "\\" . $baseName];
        });

        return $objects;
    }
}
