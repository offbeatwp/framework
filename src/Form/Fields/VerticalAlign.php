<?php

namespace OffbeatWP\Form\Fields;

class VerticalAlign extends Select
{
    public function __construct()
    {
        $this->addOptions([
            ''              => __('Default', 'pinowp'),
            'top'           => __('Top', 'pinowp'),
            'middle'        => __('Middle', 'pinowp'),
            'bottom'        => __('Bottom', 'pinowp'),
        ]);
    }

}
