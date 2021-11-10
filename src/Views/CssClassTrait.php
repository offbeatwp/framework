<?php

namespace OffbeatWP\Views;

trait CssClassTrait
{
    protected function addCssClass(string ...$classes): void
    {
        foreach ($classes as $class) {
            $this->addCssClass($class);
        }
    }

    protected function hasCssClass(string $class): bool
    {
        return in_array($class, $this->cssClasses, true);
    }

    protected function removeCssClass(string ...$classes): void
    {
        foreach ($classes as $class) {
            $this->removeCssClass($class);
        }
    }

    /** @param string[] $classes */
    protected function setCssClasses(array $classes): void
    {
        $this->cssClasses = $classes;
    }

    protected function getCssClasses(): array
    {
        return $this->cssClasses;
    }

    protected function getCssClassesAsString(): string
    {
        return implode(' ', array_filter(array_unique($this->cssClasses, SORT_STRING)));
    }
}