<?php

namespace OffbeatWP\Views;

trait CssClassTrait
{
    private $cssClasses = [];

    protected function addCssClass(string $className): void
    {
        $this->cssClasses[] = $className;
    }

    protected function addCssClasses(array $classes): void
    {
        foreach ($classes as $class) {
            $this->addCssClass($class);
        }
    }

    protected function hasClass(string $class): bool
    {
        return in_array($class, $this->cssClasses, true);
    }

    protected function removeCssClass(string $className): void
    {
        $key = array_search($className, $this->cssClasses, true);
        if ($key !== false) {
            unset($this->cssClasses[$key]);
        }
    }

    protected function removeCssClasses(array $classes): void
    {
        foreach ($classes as $class) {
            $this->removeCssClass($class);
        }
    }

    protected function setCssClasses(array $classes): void
    {
        $this->cssClasses = $classes;
    }

    protected function getCssClasses(): array
    {
        return $this->cssClasses;
    }

    protected function attachExtraCssClasesFromSettings(?object $settings, ?string $componentSlug): void
    {
        if ($settings) {
            // Add extra classes from the Gutenberg block extra-classes option
            if (isset($settings->block['className'])) {
                $additions = explode(' ', $settings->block['className']);
                if ($additions) {
                    $this->addCssClasses($additions);
                }
            }

            // Add extra classes passed through the extraClasses setting
            if (isset($settings->cssClasses)) {
                $additions = is_array($settings->cssClasses) ? $settings->cssClasses : explode(' ', $settings->cssClasses);
                if ($additions) {
                    $this->addCssClasses($additions);
                }
            }
        }

        $this->cssClasses = apply_filters('offbeatwp/component/classes',  $this->cssClasses, $componentSlug);
    }

    protected function getCssClassesAsString(): string
    {
        return implode(' ', array_filter(array_unique($this->cssClasses, SORT_STRING)));
    }
}