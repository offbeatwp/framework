<?php
namespace OffbeatWP\Form\Fields;

class Text extends AbstractField {
    public const FIELD_TYPE = 'text';

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}