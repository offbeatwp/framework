<?php
namespace OffbeatWP\Form\Fields;

final class NavMenuField extends SelectField
{
    protected function init(): void
    {
        $options = ['' => __('None', 'offbeatwp')];

        foreach (wp_get_nav_menus() as $menu) {
            $options[$menu->term_id] = $menu->name;
        }

        $this->setOptions($options);
    }
}