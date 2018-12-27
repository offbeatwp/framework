<?php

namespace OffbeatWP\Fields;

class Heading {
    public static function get($defaultHeading = 'h3', $includeLead = false)
    {
        return [
            [
                'name'          => 'heading_title',
                'label'         => __('Title', 'raow'),
                'type'          => 'text',
            ],
            [
                'name'          => 'heading_type',
                'label'         => __('Type', 'raow'),
                'type'          => 'select',
                'description'  => __('The heading type is used to let search indexers know what is important on a page', 'raow'),
                'default'       => $defaultHeading,
                'options'       => [
                    'h1' => __('h1', 'raow'),
                    'h2' => __('h2', 'raow'),
                    'h3' => __('h3', 'raow'),
                    'h4' => __('h4', 'raow'),
                    'h5' => __('h5', 'raow'),
                    'h6' => __('h6', 'raow'),
                ],
                'save_always' => true,
            ],
            [
                'name'          => 'heading_style',
                'label'         => __('Style', 'raow'),
                'type'          => 'select',
                'description'  => __('The heading style is used to override the default styling of the header type', 'raow'),
                'options'       => [
                    ''   => __('None', 'raow'),
                    'h1' => __('h1', 'raow'),
                    'h2' => __('h2', 'raow'),
                    'h3' => __('h3', 'raow'),
                    'h4' => __('h4', 'raow'),
                    'h5' => __('h5', 'raow'),
                    'h6' => __('h6', 'raow'),
                ],
                'save_always' => true,
            ],
        ];
    }
}