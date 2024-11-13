<?php

namespace OffbeatWP\Form\Fields;

class HorizontalAlign extends Select
{
    public function __construct()
    {
        $this->addOptions([
            ''              => __('Default', 'pinowp'),
            'left'          => __('Left', 'pinowp'),
            'center'        => __('Center', 'pinowp'),
            'right'         => __('Right', 'pinowp'),
        ]);
    }
}
