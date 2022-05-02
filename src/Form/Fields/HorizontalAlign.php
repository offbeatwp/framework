<?php
namespace OffbeatWP\Form\Fields;

class HorizontalAlign extends Select {
    public function init()
    {        
        $this->addOptions([
            ''              => __('Default', 'offbeatwp'),
            'left'          => __('Left', 'offbeatwp'),
            'center'        => __('Center', 'offbeatwp'),
            'right'         => __('Right', 'offbeatwp'),
        ]);
    }
}