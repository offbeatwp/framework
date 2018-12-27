<?php

namespace OffbeatWP\Fields;

class Breakpoints {
    public static function get()
    {
        return Select::get(
            'breakpoint',
            __('Breakpoints', 'raow'),
            [
                '0px'    => __('Extra Small (Mobile)', 'raow'),
                '576px'  => __('Small (Mobile)', 'raow'),
                '768px'  => __('Medium (Tablet)', 'raow'),
                '992px'  => __('Large (Desktop)', 'raow'),
                '1200px' => __('Extra Large (Desktop)', 'raow')
            ]
        );
    }
}