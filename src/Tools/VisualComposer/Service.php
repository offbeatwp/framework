<?php
namespace OffbeatWP\Tools\VisualComposer;

use OffbeatWP\Services\AbstractServicePageBuilder;

class Service extends AbstractServicePageBuilder
{

    public function register()
    {
        add_action('vc_after_init', __NAMESPACE__ . '\Builder::removeDefaultElements');
        add_action('vc_after_init', __NAMESPACE__ . '\Builder::registerSectionFieldType');
        add_action('vc_after_init', __NAMESPACE__ . '\Builder::registerAcfFieldType');
        add_action('vc_after_init', __NAMESPACE__ . '\Builder::registerHiddenFieldType');
        add_action('vc_after_init', __NAMESPACE__ . '\Builder::registerDynamicDropdown');
        add_action('vc_after_init', [$this, 'registerSettingsForm']);
    }

    public function onRegisterComponent($event)
    {
        $componentClass    = $event->getComponentClass();
        $componentSettings = $componentClass::settings();

        $vcElementConfig = [
            'name'           => $componentSettings['name'],
            'shortcode'      => 'raow_' . $componentSettings['slug'],
            'category'       => $componentSettings['category'],
            'componentClass' => $componentClass,
        ];

        if (method_exists($componentClass, 'fields')) {
            $vcElementConfig['fields'] = $componentClass::fields();
        }

        new VisualComposerElement($vcElementConfig);
    }

    public function registerSettingsForm()
    {
        $builder = new Builder();

        $builder->registerSettingsForm();
    }
}
