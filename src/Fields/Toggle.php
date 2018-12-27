<?php

namespace OffbeatWP\Fields;

class Toggle
{
    public static function get($variations, $default = null, $name = 'toggle', $label = null)
    {
        $options = [];
        $toggle = [];

        foreach ($variations as $variationKey => $variation) {
            if (!$default) {
                $default = $variationKey;
            }

            $options[$variationKey] = $variation['label'];

            $toggle[$variationKey] = [];

            if (isset($variation['fields']) && !empty($variation['fields'])) {
                $toggle[$variationKey]['fields'] = $variation['fields'];
            }

            if (isset($variation['sections']) && !empty($variation['sections'])) {
                $toggle[$variationKey]['sections'] = $variation['sections'];
            }

            if (isset($variation['tabs']) && !empty($variation['tabs'])) {
                $toggle[$variationKey]['tabs'] = $variation['tabs'];
            }
        }

        return [
            'name'         => $name,
            'label'        => (!is_null($label) ? $label : __('Toggle', 'raow')),
            'type'         => 'select',
            'default'      => $default,
            'options'      => $options,
            'toggle'       => $toggle,
        ];
    }
}