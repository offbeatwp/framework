<?php

namespace OffbeatWP\Views;

use OffbeatWP\Contracts\View;
use OffbeatWP\Foundation\App;
use ReflectionClass;

trait ViewableTrait
{
    /** @var list<non-falsy-string> */
    protected static array $loaded = [];
    protected View $view;

    /**
     * @param non-falsy-string $name
     * @param array<string, mixed> $data
     */
    public function view(string $name, array $data = [], ?View $view = null): string
    {
        $this->view = $view ?: App::getInstance()->defaultView;

        $this->setTemplatePaths();

        return $view->render($name, $data);
    }

    public function setTemplatePaths(): void
    {
        $this->setModuleViewsPath();
        $this->setRecursiveParentViewsPath();
        $this->setElementViewsPath();
    }

    public function setModuleViewsPath(): void
    {
        $name = (new ReflectionClass($this))->getName();

        if (!preg_match('/^App\\\Modules\\\([^\\\]+)/', $name, $matches)) {
            return;
        }

        if (in_array($name, static::$loaded, true)) {
            return;
        }

        $moduleClass = $matches[0] . '\\' . $matches[1];

        $module = new $moduleClass();
        $this->view->addTemplatePath($module->getViewsDirectory());

        static::$loaded[] = $name;
    }

    public function setRecursiveParentViewsPath(): void
    {
        $reflector = new ReflectionClass($this);
        $fn = $reflector->getFileName();

        $path = dirname($fn);

        $this->setRecursiveViewsPath($path, 10);
    }

    public function setRecursiveViewsPath(string $path, int $depth = 5): void
    {
        if (in_array($path, static::$loaded, true)) {
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

    public function setElementViewsPath(): void
    {
        if (!isset($this->hasViewsDirectory) || $this->hasViewsDirectory !== true) {
            return;
        }

        $reflector = new ReflectionClass($this);
        $directory = dirname($reflector->getFileName());
        $this->view->addTemplatePath($directory . '/views');
    }
}
