<?php

namespace OffbeatWP\Components;

use OffbeatWP\Foundation\App;

trait ComponentInterfaceTrait
{
    public function render($settings)
    {
        $component = App::singleton()->container->make($this->componentClass);

        if (is_array($settings)) {
            $settings = (object)$settings;
        }

        return App::singleton()->container->call([$component, 'render'], [$settings]);

    }
}
