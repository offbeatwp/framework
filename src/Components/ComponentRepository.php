<?php

namespace OffbeatWP\Components;

use OffbeatWP\Exceptions\NonexistentComponentException;
use OffbeatWP\Foundation\App;
use OffbeatWP\Layout\ContextInterface;
use OffbeatWP\Support\Wordpress\Hooks;

/** @final */
class ComponentRepository
{
    /** @var class-string<AbstractComponent>[] */
    protected array $components = [];
    protected ?ContextInterface $layoutContext = null;
    protected int $renderedComponents = 0;

    public function getLayoutContext(): ?ContextInterface
    {
        return $this->layoutContext;
    }

    /** Set the context to be distributed when rendering components. */
    public function setLayoutContext(?ContextInterface $context = null): ComponentRepository
    {
        $this->layoutContext = $context;
        return $this;
    }

    /**
     * @param string $name
     * @param class-string<AbstractComponent> $componentClass
     */
    public function register(string $name, string $componentClass): void
    {
        offbeat(Hooks::class)->doAction('offbeat.component.register', [
            'name' => $name,
            'class' => $componentClass,
        ]);

        if ($componentClass::supports('shortcode')) {
            $this->registerShortcode($name, $componentClass);
        }

        $this->components[$name] = $componentClass;
    }

    /**
     * @param string $name
     * @param class-string<AbstractComponent> $componentClass
     */
    public function registerShortcode(string $name, string $componentClass): void
    {
        $app = App::singleton();

        $tag = $componentClass::getSetting('shortcode');

        if (!$tag) {
            $tag = $componentClass::getSlug();
        }

        add_shortcode($tag, static function ($atts, $content = '') use ($app, $componentClass) {
            $shortcode = $app->getContainer()->make(GenericShortcode::class, ['componentClass' => $componentClass]);
            return $shortcode->renderShortcode($atts, $content);
        });
    }

    /**
     * @param string|null $name
     * @return class-string<AbstractComponent>|class-string<AbstractComponent>[]
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

        return container()->make($componentClass, ['context' => $this->getLayoutContext()]);
    }

    final public function exists(string $name): bool
    {
        return array_key_exists($name, $this->components);
    }

    /**
     * @param string $name
     * @param mixed[]|object $args
     * @return string|null
     * @throws NonexistentComponentException
     */
    public function render(string $name, array|object $args = []): ?string
    {
        $component = $this->make($name);
        $component->setRenderId($this->renderedComponents);

        ++$this->renderedComponents;

        return $component->renderComponent((object)$args);
    }
}
