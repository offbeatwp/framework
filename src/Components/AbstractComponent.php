<?php
namespace OffbeatWP\Components;

use OffbeatWP\Fields\Toggle;
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

        if (isset($settings['variations'])) {
            array_push($form, [
                'id'       => 'variations',
                'title'    => __('Variations', 'offbeatwp'),
                'sections' => [
                    [
                        'id'     => 'variation',
                        'title'  => __('Variations', 'raow'),
                        'fields' => Toggle::get($settings['variations'], null, 'variation', __('Variation', 'raow')),
                    ]
                ],
            ]);
        }

        return $form;
    }
}