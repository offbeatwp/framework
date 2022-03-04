<?php
namespace OffbeatWP\Form\Fields;

class VerticalAlign extends Select {
    public function __construct(string $id, string $label = '')
    {
        parent::__construct($id, $label);

        $this->addOptions([
            ''              => __('Default', 'offbeatwp'),
            'top'           => __('Top', 'offbeatwp'),
            'middle'        => __('Middle', 'offbeatwp'),
            'bottom'        => __('Bottom', 'offbeatwp'),
        ]);
    }

}