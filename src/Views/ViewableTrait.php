<?php

namespace OffbeatWP\Views;

use OffbeatWP\Contracts\View;
use OffbeatWP\Foundation\App;
use ReflectionClass;
use UnexpectedValueException;

trait ViewableTrait
{
    /** @var string[] */
    public static $loaded = [];
    /** @var mixed|View */
    public $view;

    /**
     * @param string $name
     * @param mixed[] $data
     * @return mixed
     */
    public function view(string $name, array $data = [])
    {
        $view = App::singleton()->container->get(View::class);
        $this->view = $view;

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
        $reflector = new ReflectionClass($this);

        if (!preg_match('/^App\\\Modules\\\([^\\\]+)/', $reflector->getName(), $matches)) {
            return;
        }

        if (in_array($reflector->getName(), static::$loaded, true)) {
            return;
        }

        $moduleClass = $matches[0] . '\\' . $matches[1];

        if (App::singleton()->container->has($moduleClass)) {
            $module = App::singleton()->container->get($moduleClass);

            $this->view->addTemplatePath($module->getViewsDirectory());
        }

        static::$loaded[] = $reflector->getName();
    }

    public function setRecursiveParentViewsPath(): void
    {
        $fn = (new ReflectionClass($this))->getFileName();

        if ($fn === false) {
            throw new UnexpectedValueException('ViewableTrait attempted to reflect class defined in the PHP core or in a PHP extension.');
        }

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

        $fn = (new ReflectionClass($this))->getFileName();

        if ($fn === false) {
            throw new UnexpectedValueException('ViewableTrait attempted to reflect class defined in the PHP core or in a PHP extension.');
        }

        $directory = dirname($fn);
        $this->view->addTemplatePath($directory . '/views');
    }
}
