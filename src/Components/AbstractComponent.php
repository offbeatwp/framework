<?php

namespace OffbeatWP\Components;

use Doctrine\Common\Cache\ArrayCache;
use OffbeatWP\Form\Form;
use OffbeatWP\Form\Fields\Select;
use OffbeatWP\Contracts\View;
use OffbeatWP\Layout\ContextInterface;
use OffbeatWP\Views\ViewableTrait;
use ReflectionClass;

abstract class AbstractComponent
{
    use ViewableTrait;

    /** @var View */
    public $view;
    /** @var Form|null */
    public $form = null;
    /** @var ContextInterface|null */
    protected $context;

    public function __construct(View $view, ContextInterface $context = null)
    {
        $this->view = $view;
        $this->context = $context;

        if (!offbeat()->container->has('componentCache')) {
            // Just a simple lightweight cache if none is set
            offbeat()->container->set('componentCache', new ArrayCache());
        }
    }

    public static function supports($service): bool
    {
        $settings = static::settings();
        return (array_key_exists('supports', $settings) && in_array($service, $settings['supports']));
    }

    /**
     * Specify component settings. Available settings include:
     *
     * *string* **name** - The component's display name
     *
     * *string* **description** - The component's description
     *
     * *string* **slug** - The component's slug
     *
     * *string* **category** - The category to which this component belongs to
     *
     * *string* **icon** - The name of the dash-icon that this setting will use in the editor
     *
     * *string[]* **supports** - Supported functionality of this component. Valid options include 'pagebuilder', 'editor', 'shortcode' and 'widget'.
     * @return array{name: string, description: string, slug: string, category: string, icon: string, supports: Array<string>}
     */
    abstract static function settings();

    public static function getName(): ?string
    {
        return static::getSetting('name');
    }

    /** @return string|string[]|null */
    public static function getSetting(string $key)
    {
        $settings = static::settings();

        return $settings[$key] ?? null;
    }

    public static function getDescription(): ?string
    {
        return static::getSetting('description');
    }

    public static function getCategory(): ?string
    {
        return static::getSetting('category');
    }

    public static function getIcon(): ?string
    {
        return static::getSetting('icon');
    }

    public static function getForm(): Form
    {
        $form = static::form();
        if (is_null($form)) {
            $settings = static::settings();

            if (isset($settings['form'])) {
                $form = $settings['form'];
            }

            if (!($form instanceof Form)) {
                $form = new Form();
            }
        }

        if (!empty($form) && $form instanceof Form && isset($settings['variations'])) {
            $form->addField(
                Select::make('variation', __('Variation', 'offbeatwp'))->addOptions($settings['variations'])
            );
        }

        return apply_filters('offbeatwp/component/form', $form, static::class);
    }

    /**
     * @return Form|null
     * @internal Use getForm instead
     */
    public static function form()
    {
        return null;
    }

    /**
     * Render the component.
     *
     * @param object|array $settings
     * @return string
     */
    public function renderComponent($settings)
    {
        if (!$this->isRenderable()) {
            return '';
        }

        $cachedId = $this->getCacheId($settings);
        $object = $this->getCachedComponent($cachedId);
        if ($object !== false) {
            return $object;
        }

        if ($this->context) {
            $this->context->initContext();
        }

        $output = container()->call([$this, 'render'], ['settings' => $settings]);

        $render = apply_filters('offbeat.component.render', $output, $this);
        return $this->setCachedObject($cachedId, $render);
    }

    /** Can this component be rendered? */
    public function isRenderable(): bool
    {
        return true;
    }

    protected function getCacheId($settings): string
    {
        $prefix = $this->context ? $this->context->getCacheId() : '';
        return md5($prefix . get_class($this) . json_encode($settings));
    }

    protected function getCachedComponent($id)
    {
        $object = $this->getCachedObject($id);
        if ($object !== false) {
            return $object;
        }

        return false;
    }

    protected function getCachedObject($id)
    {
        return container('componentCache')->fetch($id);
    }

    protected function setCachedObject(string $id, $object): string
    {
        container('componentCache')->save($id, (string)$object, 60);
        return (string)$object;
    }

    public function getCssClasses(object $settings): string
    {
        $classes = [];

        // Add extra classes from the Gutenberg block extra-classes option
        if (isset($settings->block['className'])) {
            $additions = explode(' ', $settings->block['className']);
            foreach ($additions as $addition) {
                $classes[] = $addition;
            }
        }

        // Add extra classes passed through the extraClasses setting
        if (isset($settings->classes)) {
            $additions = is_array($settings->classes) ? $settings->classes : explode(' ', $settings->classes);
            foreach ($additions as $addition) {
                $classes[] = $addition;
            }
        }

        $classes = apply_filters('offbeatwp/component/classes', $classes, static::getSlug());

        return implode(' ', array_filter(array_unique($classes, SORT_STRING)));
    }

    public static function getSlug(): ?string
    {
        return static::getSetting('slug');
    }

    public function getViewsDirectory(): string
    {
        return $this->getDirectory() . '/views';
    }

    public function getDirectory(): string
    {
        $classInfo = new ReflectionClass($this);

        return dirname($classInfo->getFileName());
    }
}
