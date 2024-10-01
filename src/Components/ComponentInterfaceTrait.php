<?php

namespace OffbeatWP\Components;

use OffbeatWP\Foundation\App;

trait ComponentInterfaceTrait
{
    /**
     * @param mixed[]|object $settings
     * @return string|null
     */
    public function render($settings)
    {
        $component = App::singleton()->container->make($this->componentClass);

        if (is_array($settings)) {
            $settings = (object)$settings;
        }

        return App::singleton()->container->call([$component, 'render'], [$settings]);

    }
}
