<?php

namespace OffbeatWP\Components;

trait ComponentInterfaceTrait {
    public function render(array|object $settings): ?string
    {
        $component = container()->make($this->componentClass);

        if (is_array($settings)) {
            $settings = (object)$settings;
        }

        return container()->call([$component, 'render'], [$settings]);

    }    
}