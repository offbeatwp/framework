<?php
namespace OffbeatWP\Form\Fields;

class NavMenus extends Select {
    public function getOptions () {
        $options = [
            '' => __('None', 'offbeatwp'),
        ];
        $menus = wp_get_nav_menus();

        foreach ($menus as $menu) {
            $options[$menu->term_id] = $menu->name;
        }

        return $options;
    }
}