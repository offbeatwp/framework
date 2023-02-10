<?php
namespace OffbeatWP\Form\Fields;

class Breakpoint extends Select {

    public function init(): void
    {        
        $this->addOptions([
            '0px'    => __('Extra Small (Mobile)', 'offbeatwp'),
            '576px'  => __('Small (Mobile)', 'offbeatwp'),
            '768px'  => __('Medium (Tablet)', 'offbeatwp'),
            '992px'  => __('Large (Desktop)', 'offbeatwp'),
            '1200px' => __('Extra Large (Desktop)', 'offbeatwp')
        ]);
    }
}