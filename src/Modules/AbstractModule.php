<?php

namespace OffbeatWP\Modules;

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
}
