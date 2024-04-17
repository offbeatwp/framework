<?php

namespace OffbeatWP\Components;

use OffbeatWP\Form\Form;
use OffbeatWP\Form\Fields\SelectField;
use OffbeatWP\Contracts\View;
use OffbeatWP\Layout\ContextInterface;
use OffbeatWP\Views\CssClassTrait;
use OffbeatWP\Views\ViewableTrait;
use ReflectionClass;

abstract class AbstractComponent
{
    use CssClassTrait;
    use ViewableTrait {
        ViewableTrait::view as protected traitView;
    }

    /** @var string[] */
    private $cssClasses = [];
    public ?Form $form = null;
    protected ?ContextInterface $context;
    private ?int $renderId = null;
    protected bool $assetsEnqueued = false;

    public static function form(): ?Form
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
     * *mixed[]* **block** - Block information
     *
     * *string* **icon** - The name of the dash-icon that this setting will use in the editor
     *
     * *string[]* **supports** - Supported functionality of this component. Valid options include 'pagebuilder', 'editor', 'shortcode' and 'widget'.
     * @return array{name?: string, description?: string, slug: string, category?: string, icon?: string, supports?: string[], block?: mixed[], variations?: mixed[], form?: Form}
     */
    abstract public static function settings(): array;

    public function __construct(View $view, ?ContextInterface $context = null)
    {
        $this->view = $view;
        $this->context = $context;

        if (!container()->has(ComponentArrayCache::class)) {
            // Just a simple lightweight cache if none is set
            container()->set(ComponentArrayCache::class, new ComponentArrayCache());
        }
    }

    /** @param mixed[] $data */
    public function view(string $name, array $data = []): ?string
    {
        if (!isset($data['cssClasses'])) {
            $data['cssClasses'] = $this->getCssClassesAsString();
        }

        if (!isset($data['renderId'])) {
            $data['renderId'] = $this->renderId;
        }

        return $this->traitView($name, $data);
    }

    final public function setRenderId(int $renderId): void
    {
        $this->renderId = $renderId;
    }

    /** Can this component be rendered? */
    public function isRenderable(): bool
    {
        return true;
    }

    /**
     * Render the component.
     * @param object|mixed[] $settings
     * @return string|null
     */
    public function renderComponent($settings)
    {
        if (!$this->isRenderable()) {
            return '';
        }

        $cachedId = $this->getCacheId($settings);
        $object = $this->getCachedComponent($cachedId);

        if ($object !== null) {
            return $object;
        }

        $this->context?->initContext();

        $this->attachExtraCssClassesFromSettings($settings, self::getSlug());

        if (!apply_filters('offbeat.component.should_render', true, $this, $settings)) {
            return null;
        }

        $filteredSettings = apply_filters('offbeat.component.settings', $settings, $this);
        $defaultValues = self::getForm()->getDefaultValues();

        $this->enqueueAssets();

        $output = container()->call([$this, 'render'], [
            'settings' => new ComponentSettings($filteredSettings, $defaultValues)
        ]);

        $render = apply_filters('offbeat.component.render', $output, $this);
        return $this->setCachedObject($cachedId, $render);
    }

    /** @param object|null $settings */
    private function attachExtraCssClassesFromSettings($settings, ?string $componentSlug): void
    {
        if ($settings) {
            // Add extra classes from the Gutenberg block extra-classes option
            if (isset($settings->block['className'])) {
                $additions = explode(' ', $settings->block['className']);
                $this->addCssClass(...$additions);
            }

            // Add extra classes passed through the extraClasses setting
            if (isset($settings->cssClasses)) {
                $additions = is_array($settings->cssClasses) ? $settings->cssClasses : explode(' ', $settings->cssClasses);
                if ($additions) {
                    $this->addCssClass(...$additions);
                }
            }
        }

        $this->setCssClasses(apply_filters('offbeatwp/component/classes', $this->cssClasses, $componentSlug));
    }

    /**
     * @param object|mixed[] $settings
     * @return string
     */
    final protected function getCacheId(object|array $settings): string
    {
        $prefix = $this->context ? $this->context->getCacheId() : '';
        return md5($prefix . $this::class . json_encode($settings));
    }

    final protected function getCachedComponent(string $id): ?string
    {
        return $this->getCachedObject($id);
    }

    final protected function getCachedObject(string $id): ?string
    {
        return offbeat(ComponentArrayCache::class)->fetch($id);
    }

    final protected function setCachedObject(string $id, ?string $object): ?string
    {
        if ($object) {
            offbeat(ComponentArrayCache::class)->save($id, $object, 60);
        }

        return $object;
    }

    public static function supports(string $service): bool
    {
        $settings = static::settings();
        return (array_key_exists('supports', $settings) && in_array($service, $settings['supports'], true));
    }

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

    public static function getSlug(): string
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

    public static function getIcon(): ?string
    {
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

        if ($form === null) {
            $settings = static::settings();

            if (isset($settings['form'])) {
                $form = $settings['form'];
            }

            if (!($form instanceof Form)) {
                $form = new Form();
            }
        }

        if ($form instanceof Form && isset($settings['variations'])) {
            $form->addField(
                SelectField::make('variation', __('Variation', 'offbeatwp'))->addOptions($settings['variations'])
            );
        }

        return apply_filters('offbeatwp/component/form', $form, static::class);
    }

    public function enqueueAssets(): void
    {

    }
}
