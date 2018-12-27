<?php

namespace OffbeatWP\Views;

use \OffbeatWP\Contracts\View;

trait ViewableTrait
{
    public $view;

    public function setElementViewsPath()
    {
        if (!isset($this->hasViewsDirectory) || $this->hasViewsDirectory !== true) return null;

        $reflector = new \ReflectionClass($this);
        $directory = dirname($reflector->getFileName());
        $this->view->addTemplatePath($directory . '/views');
    }

    public function setRecursiveParentViewsPath()
    {
        $reflector = new \ReflectionClass($this);
        $fn        = $reflector->getFileName();

        $path = dirname($fn);

        $itemI = 0;
        while (true) {
            $folderName = basename($path);

            if ($folderName == 'app' || $itemI > 10) {
                break;
            }

            $viewsPath = "{$path}/views/";
            if (is_dir($viewsPath)) {
                $this->view->addTemplatePath($viewsPath);
            }

            $path       = dirname($path);

            $itemI++;
        }
    }

    public function setModuleViewsPath()
    {
        $reflector = new \ReflectionClass($this);

        if (!preg_match('/^App\\\Modules\\\([^\\\]+)/', $reflector->getName(), $matches)) return null;

        $moduleClass = $matches[0] . '\\' . '' . $matches[1];

        if(container()->has($moduleClass)) {
            $module = container()->get($moduleClass);

            $this->view->addTemplatePath($module->getViewsDirectory());
        }
    }

    public function setTemplatePaths()
    {
        $this->setModuleViewsPath();
        $this->setRecursiveParentViewsPath();
        $this->setElementViewsPath();
    }

    public function view($name, $data = [])
    {
        $view       = container()->get(View::class);
        $this->view = $view;

        $this->setTemplatePaths();

        return $view->render($name, $data);
    }
}
