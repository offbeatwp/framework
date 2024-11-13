<?php

namespace OffbeatWP\Form\Fields;

class Breakpoint extends Select
{
    public function __construct()
    {
        $this->addOptions([
            '0px'    => __('Extra Small (Mobile)', 'pinowp'),
            '576px'  => __('Small (Mobile)', 'pinowp'),
            '768px'  => __('Medium (Tablet)', 'pinowp'),
            '992px'  => __('Large (Desktop)', 'pinowp'),
            '1200px' => __('Extra Large (Desktop)', 'pinowp')
        ]);
    }

}
