<?php
namespace OffbeatWP\Form\Fields;

class Radio extends AbstractOptionsField {
    public const FIELD_TYPE = 'radio';

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}