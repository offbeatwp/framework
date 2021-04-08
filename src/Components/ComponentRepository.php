<?php
namespace OffbeatWP\Components;

use Exception;
use OffbeatWP\Layout\ContextInterface;
use OffbeatWP\Layout\Frontend;

class ComponentRepository
{
    /** @var ContextInterface */
    protected $layoutContext;

    public function __construct()
    {

    }

    public function getLayoutContext()
    {
        return $this->layoutContext;
    }

    /**
     * Set the context to be distributed when rendering components.
     *
     * @param ContextInterface|null $context
     * @return $this
     */
    public function setLayoutContext(ContextInterface $context = null)
    {
        $this->layoutContext = $context;
        return $this;
    }

    public function register($name, $componentClass)
    {
        offbeat('hooks')->doAction('offbeat.component.register', [
            'name'  => $name,
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

    public function registerWidget($name, $componentClass)
    {
        $componentSettings = $componentClass::settings();

        $widgetSettings = [
            'id_base'           => $componentSettings['slug'],
            'name'              => $componentSettings['name'],
            'component_name'    => $name,
        ];

        $widget = new GenericWidget($widgetSettings, $componentClass);

        register_widget($widget);
    }

    public function registerShortcode($name, $componentClass)
    {
        $app = offbeat();

        $componentSettings = $componentClass::settings();

        $tag = $componentClass::getSetting('shortcode');

        if (empty($tag)) {
            $tag = $componentClass::getSlug();
        }

        add_shortcode($tag, function ($atts, $content = '') use ($app, $componentClass) {
            $shortcode = $app->container->make(GenericShortcode::class, ['componentClass' => $componentClass]);
            return $shortcode->renderShortcode($atts, $content);
        });
    }

    public function get($name = null)
    {
        if (is_null($name)) {
            return $this->components;
        }

        if (isset($this->components[$name])) {
            return $this->components[$name];
        }

        throw new Exception("Component does not exist ({$name})");
    }

    /**
     * @param string $name
     * @return AbstractComponent
     * @throws Exception
     */
    public function make($name)
    {
        $componentClass = $this->get($name);

        return offbeat()->container->make($componentClass, ['context' => $this->getLayoutContext()]);
    }

    public function exists($name)
    {
        if (isset($this->components[$name])) {
            return true;
        }

        return false;
    }

    public function raw($name, $args = [])
    {
        $component = $this->make($name);
        return $component->rawComponent((object)$args);
    }

    public function render($name, $args = [])
    {
        $component = $this->make($name);
        return $component->renderComponent((object)$args);
    }
}
