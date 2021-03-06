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
    /** @var ContextInterface|null */
    protected $context;
    /** @var Form|null */
    public $form = null;

	/**
     * @internal Use getForm instead
     * @return Form|null
     */
	static public function form()
    {
		return null;
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
     * @return string[]|string[][]
     */
	abstract static function settings();

    public function __construct(View $view, ContextInterface $context = null)
    {
        $this->view = $view;
        $this->context = $context;

        if (!offbeat()->container->has('componentCache')) {
            // just a simple light weight cache if none is set
            offbeat()->container->set('componentCache', new ArrayCache());
        }
    }

    /** Can this component be rendered? */
    public function isRenderable(): bool
    {
        return true;
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

    public static function supports($service): bool
    {
        $settings = static::settings();
        return (array_key_exists('supports', $settings) && in_array($service, $settings['supports']));
    }

    /** @return string|string[]|null */
    public static function getSetting(string $key)
    {
        $settings = static::settings();

        return $settings[$key] ?? null;
    }

    public static function getName(): ?string
    {
        return static::getSetting('name');
    }

    public static function getSlug(): ?string
    {
        return static::getSetting('slug');
    }

    public static function getDescription(): ?string
    {
        return static::getSetting('description');
    }

    public static function getCategory(): ?string
    {
        return static::getSetting('category');
    }

    public static function getIcon(): ?string {
        return static::getSetting('icon');
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

    public static function getForm(): Form
    {
	    $form = static::form();
	    if(is_null($form)) {
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
}
