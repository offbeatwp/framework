<?php

namespace OffbeatWP\Form\Fields;

class NavMenu extends Select
{
    public function __construct()
    {
        $this->addOptions([$this, 'getNavMenus']);
    }

    /** @return array<int, string> */
    public function getNavMenus()
    {
        $options = [
            '' => __('None', 'pinowp'),
        ];
        $menus = wp_get_nav_menus();

        foreach ($menus as $menu) {
            $options[$menu->term_id] = $menu->name;
        }

        return $options;
    }
}
