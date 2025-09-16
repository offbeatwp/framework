<?php

namespace OffbeatWP\Views;

use OffbeatWP\Contracts\View;
use ReflectionClass;
use RuntimeException;

trait ViewableTrait
{
    /** @var list<string> */
    protected static array $loaded = [];
    protected ?View $viewRenderer = null;

    /**
     * @param non-falsy-string $name
     * @param array<string, mixed> $data
     */
    public function view(string $name, array $data = []): string
    {
        $this->setRecursiveParentViewsPath();

        return $this->getViewRenderer()->render($name, $data);
    }

    final protected function setRecursiveParentViewsPath(): void
    {
        $reflector = new ReflectionClass($this);
        /** @var string $fn */
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
                $this->getViewRenderer()->addTemplatePath($viewsPath);
            }

            $itemI++;

            if ($itemI > $depth || get_stylesheet_directory() === $path) {
                break;
            }

            $path = dirname($path);
        }

        static::$loaded[] = $path;
    }

    private function getViewRenderer(): View
    {
        if ($this->viewRenderer === null) {
            $viewRenderer = apply_filters('offbeatwp_view_renderer', null);

            if (!$viewRenderer instanceof View) {
                throw new RuntimeException('No view renderer available.');
            }

            $this->viewRenderer = $viewRenderer;
        }

        return $this->viewRenderer;
    }
}
