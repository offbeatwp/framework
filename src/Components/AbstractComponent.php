<?php

namespace OffbeatWP\Components;

use OffbeatWP\Form\Form;
use OffbeatWP\Contracts\View;
use OffbeatWP\Foundation\App;
use OffbeatWP\Views\CssClassTrait;
use OffbeatWP\Views\ViewableTrait;
use ReflectionClass;

abstract class AbstractComponent
{
    use CssClassTrait;
    use ViewableTrait {
        ViewableTrait::view as protected traitView;
    }

    /** @var View */
    public $view;
    public ?Form $form = null;
    protected bool $assetsEnqueued = false;
    private ?int $renderId = null;
    /** @var string[] */
    private array $cssClasses = [];

    /** @return Form|null */
    public static function form()
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
    abstract public static function settings();

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * @param string $name
     * @param mixed[] $data
     * @return string|null
     */
    public function view(string $name, array $data = [])
    {
        if (!isset($data['cssClasses'])) {
            $data['cssClasses'] = $this->getCssClassesAsString();
        }

        if (!isset($data['renderId'])) {
            $data['renderId'] = $this->renderId;
        }

        return $this->traitView($name, $data);
    }

    public function setRenderId(int $renderId): void
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
     * @param object $settings
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

        $this->attachExtraCssClassesFromSettings($settings, self::getSlug());

        if (!apply_filters('offbeat.component.should_render', true, $this, $settings)) {
            return null;
        }

        $filteredSettings = apply_filters('offbeat.component.settings', $settings, $this);
        $defaultValues = self::getForm()->getDefaultValues();

        static::_enqueueAssets();

        $output = App::singleton()->container->call([$this, 'render'], [
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
    protected function getCacheId($settings): string
    {
        return md5($this::class . json_encode($settings));
    }

    protected function getCachedComponent(string $id): ?string
    {
        return $this->getCachedObject($id);
    }

    protected function getCachedObject(string $id): ?string
    {
        return ComponentArrayCache::fetch($id);
    }

    protected function setCachedObject(string $id, ?string $object): ?string
    {
        if ($object) {
            ComponentArrayCache::save($id, $object, 60);
        }

        return $object;
    }

    public static function supports(string $service): bool
    {
        return false;
    }

    public static function getName(): ?string
    {
        return static::getSetting('name');
    }

    /** @return string|string[]|null */
    public static function getSetting(string $key)
    {
        return null;
    }

    public static function getSlug(): string
    {
        return static::getSetting('slug') ?? basename(static::class);
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
        return apply_filters('offbeatwp/component/form', new Form(), static::class);
    }

    /** @return void */
    public static function enqueueAssets()
    {
    }

    /** @return void */
    public static function _enqueueAssets()
    {
        static::enqueueAssets();
    }
}
