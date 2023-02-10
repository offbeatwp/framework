<?php
namespace OffbeatWP\Form\Fields;

class ButtonGroup extends AbstractOptionsField {
    public const FIELD_TYPE = 'button_group';

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}