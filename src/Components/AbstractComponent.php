<?php
namespace OffbeatWP\Components;

use OffbeatWP\Form\Form;
use OffbeatWP\Form\Fields\Select;
use OffbeatWP\Contracts\View;
use OffbeatWP\Views\ViewableTrait;

abstract class AbstractComponent
{
    use ViewableTrait;

    public $view;
    public $form = null;

    public function __construct (View $view) {
        $this->view = $view;
    }

    public static function supports($service)
    {
        if(!method_exists(get_called_class(), 'settings')) return false;

        $componentSettings = static::settings();

        if (!array_key_exists('supports', $componentSettings) || ! in_array($service, $componentSettings['supports'])) return false;

        return true;
    }

    public static function getSetting($key){
        if(!method_exists(get_called_class(), 'settings')) return false;

        $componentSettings = static::settings();

        return isset($componentSettings[$key]) ? $componentSettings[$key] : null;
    }

    public static function getName()
    {
        return static::getSetting('name');
    }

    public static function getSlug()
    {
        return static::getSetting('slug');
    }

    public static function getDescription()
    {
        return static::getSetting('description');
    }

    public function getViewsDirectory()
    {
        return $this->getDirectory() . '/views';
    }

    public function getDirectory()
    {
        $classInfo = new \ReflectionClass($this);

        return dirname($classInfo->getFileName());
    }

    public static function getForm()
    {
        if (!method_exists(get_called_class(), 'settings')) return [];

        $form = null;
        $settings = static::settings();

        if (isset($settings['form']))
            $form = $settings['form'];

        if (!($form instanceof Form)) {
            $form = new Form();
        }

        if (!empty($form) && $form instanceof Form && isset($settings['variations'])) {
            $form->addField(
                Select::make(
                    'variation',
                    __('Variation', 'offbeatwp')
                )->addOptions($settings['variations'])
            );
        }

        $form = apply_filters('offbeatwp/component/form', $form, static::class);

        return $form;
    }
}