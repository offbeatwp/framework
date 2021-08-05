<?php

namespace OffbeatWP\Views;

use OffbeatWP\Contracts\View;
use ReflectionClass;

trait ViewableTrait
{
    public static $loaded = [];
    public $view;

    public function view($name, $data = [])
    {
        $view = container()->get(View::class);
        $this->view = $view;

        $this->setTemplatePaths();

        return $view->render($name, $data);
    }

    public function setTemplatePaths()
    {
        $this->setModuleViewsPath();
        $this->setRecursiveParentViewsPath();
        $this->setElementViewsPath();
    }

    public function setModuleViewsPath()
    {
        $reflector = new ReflectionClass($this);

        if (!preg_match('/^App\\\Modules\\\([^\\\]+)/', $reflector->getName(), $matches)) {
            return;
        }

        if (in_array($reflector->getName(), static::$loaded)) {
            return;
        }

        $moduleClass = $matches[0] . '\\' . '' . $matches[1];

        if (container()->has($moduleClass)) {
            $module = container()->get($moduleClass);

            $this->view->addTemplatePath($module->getViewsDirectory());
        }

        static::$loaded[] = $reflector->getName();
    }

    public function setRecursiveParentViewsPath()
    {
        $reflector = new ReflectionClass($this);
        $fn = $reflector->getFileName();

        $path = dirname($fn);

        $this->setRecursiveViewsPath($path, 10);
    }

    public function setRecursiveViewsPath($path, $depth = 5)
    {
        if (in_array($path, static::$loaded)) {
            return;
        }

        $itemI = 0;
        while (true) {
            $viewsPath = "{$path}/views/";
            if (is_dir($viewsPath)) {
                $this->view->addTemplatePath($viewsPath);
            }

            $itemI++;

            if ($itemI > $depth || get_stylesheet_directory() === $path) {
                break;
            }

            $path = dirname($path);
        }

        static::$loaded[] = $path;
    }

    public function setElementViewsPath()
    {
        if (!isset($this->hasViewsDirectory) || $this->hasViewsDirectory !== true) {
            return;
        }

        $reflector = new ReflectionClass($this);
        $directory = dirname($reflector->getFileName());
        $this->view->addTemplatePath($directory . '/views');
    }
}
