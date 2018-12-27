<?php

namespace OffbeatWP\Modules;

use OffbeatWP\Commands\Commands;
use OffbeatWP\Services\AbstractService;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class AbstractModule extends AbstractService
{
    public $app;

    public function __construct($app)
    {
        $this->app = $app;
        parent::__construct($app);

        if (method_exists($this, 'boot')) {
            $eventDispatcher->addListener('raow.ready', [$this, 'boot']);
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

    public function getName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    public function registerComponents()
    {
        $directory = $this->getDirectory() . '/Components';

        $registerableComponents = $this->getRegisterableObjects($directory, true);

        if (!empty($registerableComponents)) {
            foreach ($registerableComponents as $name => $class) {
                $componentName = lcfirst($this->getName()) . '.' . lcfirst($name);

                offbeat('components')->register($componentName, $class);
            }
        }
    }

    public function getNamespace()
    {
        $classInfo = new \ReflectionClass($this);
        return substr($classInfo->name, 0, strrpos($classInfo->name, "\\"));
    }

    public function getDirectory()
    {
        $classInfo = new \ReflectionClass($this);
        $classPath = $classInfo->getFileName();

        return dirname($classPath);
    }

    public function getRegisterableObjects($path, $findDirs = false)
    {
        $objects = [];

        if (!is_dir($path)) {
            return null;
        }

        $paths = glob($path . '/*', GLOB_ONLYDIR);
        $objects = collect($paths)->filter(function($path) {
            return !preg_match('/^_/', basename($path));
        })->mapWithKeys(function ($path) {
            $baseName = basename($path);
            
            return [$baseName => $this->getNamespace() . "\Components\\" . $baseName . "\\" . $baseName];
        });

        return $objects;
    }
}
