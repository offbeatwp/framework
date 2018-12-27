<?php
namespace OffbeatWP\Fields;

class Textalign {
    public static function get()
    {
        return [
            'name'          => 'text_align',
            'label'         => __('Text align', 'raow'),
            'type'          => 'select',
            'options'       => [
                ''              => __('Default', 'raow'),
                'left'          => __('Left', 'raow'),
                'center'        => __('Center', 'raow'),
                'right'         => __('Right', 'raow'),
            ],
        ];
    }
}