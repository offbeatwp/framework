<?php

namespace OffbeatWP\Views;

trait CssClassTrait
{
    protected function addCssClass(string ...$classes): void
    {
        foreach ($classes as $class) {
            $this->cssClasses[] = $class;
        }
    }

    protected function hasCssClass(string $class): bool
    {
        return in_array($class, $this->cssClasses, true);
    }

    protected function removeCssClass(string ...$classes): void
    {
        foreach ($classes as $class) {
            $key = array_search($class, $this->cssClasses, true);
            if ($key !== false) {
                unset($this->cssClasses[$key]);
            }
        }
    }

    /** @param string[] $classes */
    protected function setCssClasses(array $classes): void
    {
        $this->cssClasses = $classes;
    }

    /** @return string[] */
    protected function getCssClasses(): array
    {
        return $this->cssClasses;
    }

    protected function getCssClassesAsString(): string
    {
        return implode(' ', array_filter(array_unique($this->cssClasses)));
    }
}