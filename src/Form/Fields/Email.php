<?php

namespace OffbeatWP\Form\Fields;

class Email extends AbstractField
{
    public const FIELD_TYPE = 'email';

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}
