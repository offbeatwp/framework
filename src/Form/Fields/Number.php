<?php
namespace OffbeatWP\Form\Fields;

class Number extends AbstractField {
    public const FIELD_TYPE = 'number';

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}