<?php
namespace OffbeatWP\Form\Fields;

class Select extends AbstractOptionsField {
    public const FIELD_TYPE = 'select';

    public $options = [];

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}