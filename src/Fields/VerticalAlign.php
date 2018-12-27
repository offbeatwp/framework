<?php

namespace OffbeatWP\Fields;

class VerticalAlign {
    public static function get($name = 'vert_align')
    {
        return [
            'name'          => $name,
            'label'         => __('Vertical align', 'raow'),
            'type'          => 'select',
            'options'       => [
                ''              => __('Default', 'raow'),
                'top'           => __('Top', 'raow'),
                'middle'        => __('Middle', 'raow'),
                'bottom'        => __('Bottom', 'raow'),
            ],
        ];
    }
}