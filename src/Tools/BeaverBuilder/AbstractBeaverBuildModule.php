<?php

namespace OffbeatWP\Tools\BeaverBuilder;

use OffbeatWP\Components\ComponentInterfaceTrait;

abstract class AbstractBeaverBuildModule extends \FLBuilderModule
{
    use ComponentInterfaceTrait;

    public function __construct()
    {
        $componentClass = preg_replace('#\\\([^\\\]+)\\\Support\\\.*#', '\\\$1\\\$1', get_called_class());;
        $componentSettings = $componentClass::settings();

        $args = [
            'name'        => $componentSettings['name'],
            'slug'        => $componentSettings['slug'],
            'category'    => $componentSettings['category'],
            'description' => $componentSettings['name'],
        ];

        if(isset($componentSettings['description']))
            $args['description'] = $componentSettings['description'];

        parent::__construct($args);

        $this->dir = dirname(__FILE__) . '/includes/beaverbuilder/';
        $this->slug = 'raow_' . $componentSettings['slug'];

        $this->componentClass = $componentClass;
    }
}