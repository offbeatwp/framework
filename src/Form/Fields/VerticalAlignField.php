<?php
namespace OffbeatWP\Form\Fields;

final class VerticalAlignField extends SelectField
{
    protected function init(): void
    {        
        $this->setOptions([
            ''              => __('Default', 'offbeatwp'),
            'top'           => __('Top', 'offbeatwp'),
            'middle'        => __('Middle', 'offbeatwp'),
            'bottom'        => __('Bottom', 'offbeatwp'),
        ]);
    }

}