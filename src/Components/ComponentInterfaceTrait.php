<?php

namespace OffbeatWP\Components;

trait ComponentInterfaceTrait {
    public function render($settings)
    {
        $component = container()->make($this->componentClass);

        if (is_array($settings)) 
            $settings = (object) $settings;

        return container()->call([$component, 'render'], [$settings]);

    }    
}