<?php
namespace OffbeatWP\Form\Fields;

final class Textalign extends SelectField {

    protected function init(): void
    {        
        $this->setOptions([
            ''              => __('Default', 'offbeatwp'),
            'left'          => __('Left', 'offbeatwp'),
            'center'        => __('Center', 'offbeatwp'),
            'right'         => __('Right', 'offbeatwp'),
        ]);
    }

}