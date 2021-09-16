<?php
namespace OffbeatWP\Form\Fields;

class NavMenu extends Select {

    public function __construct()
    {        
        $this->addOptions([$this, 'getNavMenus']);
    }

    public function getNavMenus (): array
    {
        $options = ['' => __('None', 'offbeatwp')];
        $menus = wp_get_nav_menus();

        foreach ($menus as $menu) {
            $options[$menu->term_id] = $menu->name;
        }

        return $options;
    }
}