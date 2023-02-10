<?php
namespace OffbeatWP\Form\Fields;

class NavMenu extends Select {
    public function getOptions(): array
    {
        $options = ['' => __('None', 'offbeatwp')];
        $menus = wp_get_nav_menus();

        foreach ($menus as $menu) {
            $options[$menu->term_id] = $menu->name;
        }

        return $options;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}