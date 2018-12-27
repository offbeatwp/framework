<?php

namespace OffbeatWP\Fields;

class NavMenus {
    public static function get($name, $label, $attr = [])
    {
        $field              = $attr;
        $field['name']      = $name;
        $field['label']     = $label;
        $field['type']      = 'select';
        $field['options']   = __NAMESPACE__ . '\NavMenus::options';

        return [$field];
    }

    public static function options()
    {
        $options = [
            '' => __('None', 'raow'),
        ];
        $menus = wp_get_nav_menus();

        foreach ($menus as $menu) {
            $options[$menu->term_id] = $menu->name;
        }

        return $options;
    }
}