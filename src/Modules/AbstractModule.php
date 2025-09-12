<?php

namespace OffbeatWP\Modules;

use OffbeatWP\Foundation\App;
use OffbeatWP\Services\AbstractService;
use ReflectionClass;

abstract class AbstractModule extends AbstractService
{
    public function __construct(App $app)
    {
        parent::__construct($app);

        if (method_exists($this, 'boot')) {
            add_action('raow.ready', [$this, 'boot']);
        }

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
        $path = str_replace(get_stylesheet_directory(), '', $this->getDirectory());

        return get_stylesheet_directory_uri() . $path;
    }
}
