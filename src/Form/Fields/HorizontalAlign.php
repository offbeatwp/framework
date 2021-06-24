<?php

namespace OffbeatWP\Form\Fields;

class HorizontalAlign extends Select
{
    public function __construct()
    {
        $this->addOptions([
            '' => __('Default', 'offbeatwp'),
            'left' => __('Left', 'offbeatwp'),
            'center' => __('Center', 'offbeatwp'),
            'right' => __('Right', 'offbeatwp'),
        ]);
    }
}