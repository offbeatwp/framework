<?php

namespace OffbeatWP\SiteSettings;

class SettingsScripts
{
    const ID = 'scripts';
    const PRIORITY = 90;

    public function title()
    {
        return __('Scripts', 'raow');
    }

    public function fields()
    {
        return [[
            'id'     => 'contact',
            'title'  => 'Core settings',
            'fields' => [
                [
                    'name'        => 'scripts_head',
                    'label'       => 'Head',
                    'type'        => 'textarea',
                    'default'     => '',
                    'placeholder' => '',
                ],
                [
                    'name'        => 'scripts_open_body',
                    'label'       => 'Body open',
                    'type'        => 'textarea',
                    'default'     => '',
                    'placeholder' => '',
                ],
                [
                    'name'        => 'scripts_footer',
                    'label'       => 'Footer',
                    'type'        => 'textarea',
                    'default'     => '',
                    'placeholder' => '',
                ],
            ],
        ],
        ];
    }
}