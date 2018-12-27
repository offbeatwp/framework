<?php

namespace OffbeatWP\Fields;

class ButtonStyle {
    public static function get()
    {
        return array_merge([
            [
                'name'          => 'button_size',
                'label'         => __('Button size', 'raow'),
                'type'          => 'select',
                'default'       => 'normal',
                'options'       => [
                    'normal'   => __('Normal', 'raow'),
                    'small'    => __('Small', 'raow'),
                    'large'    => __('Large', 'raow'),
                ],
            ],
            [
                'name'          => 'button_style',
                'label'         => __('Button style', 'raow'),
                'type'          => 'select',
                'default'       => 'btn-primary',
                'options'       => [
                    'btn-primary'           => __('Primary', 'raow'),
                    'btn-secondary'         => __('Secondary', 'raow'),
                    'btn-outline-primary'   => __('Outline primary', 'raow'),
                    'btn-outline-dark'      => __('Outline dark', 'raow'),
                ],
            ],
            [
                'name'          => 'has_arrow',
                'label'         => __('Show arrow', 'raow'),
                'type'          => 'true_false',
                'default'       => false
            ]
        ], Textalign::get());
    }
}