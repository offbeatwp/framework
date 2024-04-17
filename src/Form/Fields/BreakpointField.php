<?php
namespace OffbeatWP\Form\Fields;

final class BreakpointField extends SelectField
{
    protected function init(): void
    {
        $this->setOptions([
            '0px'    => __('Extra Small (Mobile)', 'offbeatwp'),
            '576px'  => __('Small (Mobile)', 'offbeatwp'),
            '768px'  => __('Medium (Tablet)', 'offbeatwp'),
            '992px'  => __('Large (Desktop)', 'offbeatwp'),
            '1200px' => __('Extra Large (Desktop)', 'offbeatwp')
        ]);
    }

}