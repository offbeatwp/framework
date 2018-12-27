<?php

namespace OffbeatWP\Fields;

class HorizontalAlign {
    public static function get($name = 'horz_align')
    {
        return [
            [
                'name'          => $name,
                'label'         => __('Horizontal align', 'raow'),
                'type'          => 'select',
                'options'       => [
                    ''              => __('Default', 'raow'),
                    'left'          => __('Left', 'raow'),
                    'center'        => __('Center', 'raow'),
                    'right'         => __('Right', 'raow'),
                ],
            ]
        ];
    }
}