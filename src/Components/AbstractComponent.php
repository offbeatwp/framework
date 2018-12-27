<?php
namespace OffbeatWP\Components;

use OffbeatWP\Fields\Toggle;
use OffbeatWP\Contracts\View;
use OffbeatWP\Fields\DisplaySettings;
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

        $form = [];
        $settings = static::settings();

        if (isset($settings['form']))
            $form = $settings['form'];

        array_push($form, DisplaySettings::get($settings));

        return $form;
    }
}