<?php

namespace OffbeatWP\Fields;

class Link
{
    public static function get($advanced = true)
    {
        $return = [];

        $return[] = [
            'name'  => 'link_title',
            'label' => __('Link title', 'raow'),
            'type'  => 'text',
        ];

        if ($advanced) {
            $return[] = [
                'name'    => 'link_type',
                'label'   => __('Link type', 'raow'),
                'type'    => 'select',
                'default' => 'link',
                'options' => [
                    'link'     => __('Link', 'raow'),
                    'modal'    => __('Modal', 'raow'),
                    'tab'      => __('Tab', 'raow'),
                    'collapse' => __('Collapse', 'raow'),
                ],
                'toggle'  => [
                    'link'     => [
                        'fields' => [
                            'link',
                            'link_target',
                        ],
                    ],
                    'modal'    => [
                        'fields' => [
                            'data_target',
                        ],
                    ],
                    'tab'      => [
                        'fields' => [
                            'data_target',
                        ],
                    ],
                    'collapse' => [
                        'fields' => [
                            'data_target',
                        ],
                    ],
                ],
            ];
        }

        $return[] = [
            'name'  => 'link',
            'label' => __('Link', 'raow'),
            'type'  => 'link',
        ];

        if ($advanced) {
            $return[] = [
                'name' => 'link_target',
                'label' => __('Link type', 'raow'),
                'type' => 'select',
                'default' => '_self',
                'options' => [
                    '_self' => __('Self', 'raow'),
                    '_blank' => __('Blank', 'raow'),
                    '_parent' => __('Parent', 'raow'),
                    '_top' => __('Top', 'raow'),
                ],
            ];

            $return[] = [
                'name' => 'data_target',
                'label' => __('Link target', 'raow'),
                'type' => 'text',
            ];
        }

        return $return;
    }
}