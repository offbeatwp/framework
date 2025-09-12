<?php

namespace OffbeatWP\Views;

use BadMethodCallException;
use OffbeatWP\Contracts\View;
use OffbeatWP\Foundation\App;
use ReflectionClass;

trait ViewableTrait
{
    /** @var list<non-falsy-string> */
    protected static array $loaded = [];
    protected ?View $viewRenderer = null;

    /**
     * @param non-falsy-string $name
     * @param array<string, mixed> $data
     */
    public function view(string $name, array $data = []): string
    {
        if ($this->viewRenderer === null) {
            $this->viewRenderer = App::getInstance()->viewRenderer;
        }

        if ($this->viewRenderer === null) {
            throw new BadMethodCallException('No view renderer instance available.');
        }

        $this->setModuleViewsPath();
        $this->setRecursiveParentViewsPath();

        return $this->viewRenderer->render($name, $data);
    }

    final public function setRenderer(View $viewRenderer): void
    {
        $this->viewRenderer = $viewRenderer;
    }

    final protected function setModuleViewsPath(): void
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
        $this->viewRenderer->addTemplatePath($module->getViewsDirectory());

        static::$loaded[] = $name;
    }

    final protected function setRecursiveParentViewsPath(): void
    {
        $reflector = new ReflectionClass($this);
        $fn = $reflector->getFileName();

        $path = dirname($fn);

        $this->setRecursiveViewsPath($path, 10);
    }

    final public function setRecursiveViewsPath(string $path, int $depth = 5): void
    {
        if (in_array($path, static::$loaded, true)) {
            return;
        }

        $itemI = 0;
        while (true) {
            $viewsPath = "{$path}/views/";
            if (is_dir($viewsPath)) {
                $this->viewRenderer->addTemplatePath($viewsPath);
            }

            $itemI++;

            if ($itemI > $depth || get_stylesheet_directory() === $path) {
                break;
            }

            $path = dirname($path);
        }

        static::$loaded[] = $path;
    }
}
