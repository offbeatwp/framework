<?php
namespace OffbeatWP\Form\Fields;

class HorizontalAlign extends Select {
    public function init(): void
    {        
        $this->setOptions([
            ''              => __('Default', 'offbeatwp'),
            'left'          => __('Left', 'offbeatwp'),
            'center'        => __('Center', 'offbeatwp'),
            'right'         => __('Right', 'offbeatwp'),
        ]);
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}