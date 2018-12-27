<?php

namespace OffbeatWP\Tools\GravityForms\Components;

class GravityForm extends \OffbeatWP\Components\AbstractComponent
{
    public static function settings() {
        return [
            'name'      => __('Gravity Form', 'raow'),
            'slug'      => 'gravityform',
            'category'  => __('Basic Modules', 'raow'),
            'supports'  => ['widget', 'pagebuilder'],
            'form'      => self::form(),
        ];
    }

    public function render($settings)
    {
        return gravity_form($settings->formId, $settings->displayTitle, $settings->displayDescription, false, null, true, 1, false);
    }

    public static function form() {
        return [[
            'id'  => 'general',
            'title'  => __('General', 'raow'),
            'sections' => [[
                'id' => 'general',
                'title'  => __('Form', 'raow'),
                'fields' => [
                    [
                        'name' => 'form',
                        'label' => __('Form', 'raow'),
                        'type' => 'gravityforms',
                    ]
                ]
            ]]
        ]];
    }
}