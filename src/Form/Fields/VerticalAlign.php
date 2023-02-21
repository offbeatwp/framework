<?php
namespace OffbeatWP\Form\Fields;

class VerticalAlign extends Select {

    public function init(): void
    {        
        $this->setOptions([
            ''              => __('Default', 'offbeatwp'),
            'top'           => __('Top', 'offbeatwp'),
            'middle'        => __('Middle', 'offbeatwp'),
            'bottom'        => __('Bottom', 'offbeatwp'),
        ]);
    }
}