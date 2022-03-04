<?php
namespace OffbeatWP\Form\Fields;

class HorizontalAlign extends Select {

    public function __construct(string $id, string $label = '')
    {
        parent::__construct($id, $label);

        $this->addOptions([
            ''              => __('Default', 'offbeatwp'),
            'left'          => __('Left', 'offbeatwp'),
            'center'        => __('Center', 'offbeatwp'),
            'right'         => __('Right', 'offbeatwp'),
        ]);
    }
}