<?php

namespace OffbeatWP\Tools\BeaverBuilder;

use OffbeatWP\Services\AbstractServicePageBuilder;
use OffbeatWP\Contracts\View;

class Service extends AbstractServicePageBuilder
{
    public $components = [];

    public function afterRegister(View $view)
    {
        if (!class_exists('\FLBuilderModel')) {
            return false;
        }

        add_action('init', [$this, 'registerModules'], 80);

        $builder = $this->app->container->make(Builder::class);
        $view->registerGlobal('bb', new Helpers\ViewHelpers());

        add_filter('fl_builder_register_settings_form', [$builder, 'registerSettingsForm'], 999, 2);
        add_filter('fl_builder_register_module',        [$builder, 'deregisterModules'], 999, 2);

        if (!is_admin()) {
            add_action('wp_enqueue_scripts',                [$this, 'enqueueScripts'], 999);
            add_filter('fl_builder_render_module_content',  [$this, 'deferJavascript'], 10, 2);
            add_filter('fl_builder_row_custom_class',       [$builder, 'addCustomClasses'], 999, 2);
            add_filter('fl_builder_module_custom_class',    [$builder, 'addCustomClasses'], 999, 2);
        }

        add_filter('fl_ajax_fl_builder_autosuggest', [ThirdParty\Polylang::class, 'filterFlAsPosts'], 20, 2);
    }

    public function onRegisterComponent($event)
    {
        $this->components[] = $event->getComponentClass();
    }

    public function registerModules()
    {
        if (!empty($this->components)) {
            foreach ($this->components as $component) {
                $componentSettings = $component::settings();

                $componentClass = new \ReflectionClass($component);
                $bbClassName = $componentClass->getNamespaceName() . '\Support\BeaverBuilderModule';

                $form = [];

                $formFields = $component::getForm();
                if (!empty($formFields)) {
                    $fieldsMapper = new FieldsMapper($formFields);
                    $form = $fieldsMapper->map();
                }

                \FLBuilder::register_module($bbClassName, $form);
            }
        }
    }

    public function enqueueScripts()
    {
        remove_action('wp_footer', 'FLBuilder::include_jquery');

        wp_deregister_style('bootstrap-tour');
    }

    public function deferJavascript($out, $module)
    {
        if ($module instanceof \FLNumbersModule) {
            $out = preg_replace('/<script>(.*)<\/script>/U',
                '<script>document.addEventListener("DOMContentLoaded", function() { $1 });</script>', $out);
        }

        return $out;
    }
}