<?php
namespace OffbeatWP\Form\Fields;

final class NumberField extends AbstractField
{
    public const FIELD_TYPE = 'number';

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}