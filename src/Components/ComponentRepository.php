<?php

namespace OffbeatWP\Components;

use OffbeatWP\Exceptions\NonexistentComponentException;
use OffbeatWP\Layout\ContextInterface;

class ComponentRepository
{
    /** @var ContextInterface|null */
    protected $layoutContext;
    /** @var class-string<AbstractComponent>[] */
    protected $components = [];
    /** @var int */
    protected $renderedComponents = 0;

    public function getLayoutContext(): ?ContextInterface
    {
        return $this->layoutContext;
    }

    /**
     * Set the context to be distributed when rendering components.
     *
     * @param ContextInterface|null $context
     * @return $this
     */
    public function setLayoutContext(ContextInterface $context = null): ComponentRepository
    {
        $this->layoutContext = $context;

        return $this;
    }

    /**
     * @param string $name
     * @param class-string<AbstractComponent> $componentClass
     */
    public function register($name, $componentClass)
    {
        offbeat('hooks')->doAction('offbeat.component.register', [
            'name' => $name,
            'class' => $componentClass,
        ]);

        if ($componentClass::supports('widget')) {
            $this->registerWidget($name, $componentClass);
        }

        if ($componentClass::supports('shortcode')) {
            $this->registerShortcode($name, $componentClass);
        }

        $this->components[$name] = $componentClass;
    }

    /**
     * @param string $name
     * @param class-string<AbstractComponent> $componentClass
     */
    public function registerWidget($name, $componentClass)
    {
        $componentSettings = $componentClass::settings();

        $widgetSettings = [
            'id_base' => $componentSettings['slug'],
            'name' => $componentSettings['name'],
            'component_name' => $name,
        ];

        $widget = new GenericWidget($widgetSettings, $componentClass);

        register_widget($widget);
    }

    /**
     * @param string $name
     * @param class-string<AbstractComponent> $componentClass
     */
    public function registerShortcode($name, $componentClass)
    {
        $app = offbeat();

        $tag = $componentClass::getSetting('shortcode');

        if (!$tag) {
            $tag = $componentClass::getSlug();
        }

        add_shortcode($tag, static function ($atts, $content = '') use ($app, $componentClass) {
            $shortcode = $app->container->make(GenericShortcode::class, ['componentClass' => $componentClass]);
            return $shortcode->renderShortcode($atts, $content);
        });
    }

    /**
     * @param string|null $name
     * @return class-string<AbstractComponent>|class-string<AbstractComponent>[]|null
     * @throws NonexistentComponentException
     */
    public function get($name = null)
    {
        if ($name === null) {
            return $this->components;
        }

        if (isset($this->components[$name])) {
            return $this->components[$name];
        }

        throw new NonexistentComponentException("Component does not exist ({$name})");
    }

    /** @return class-string<AbstractComponent>[] */
    public function getAll(): array
    {
        return $this->components;
    }

    /**
     * @param string $name
     * @return mixed|AbstractComponent
     * @throws NonexistentComponentException
     */
    public function make($name)
    {
        $componentClass = $this->get($name);

        return offbeat()->container->make($componentClass, ['context' => $this->getLayoutContext()]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function exists($name): bool
    {
        return isset($this->components[$name]);
    }

    /**
     * @param string $name
     * @param array|object $args
     * @return string|null
     * @throws NonexistentComponentException
     */
    public function render($name, $args = [])
    {
        $component = $this->make($name);
        $component->setRenderIndex($this->renderedComponents);

        ++$this->renderedComponents;

        return $component->renderComponent((object)$args);
    }
}
