<?php

namespace OffbeatWP\Form\Fields;

class UrlField extends AbstractField
{
    public const FIELD_TYPE = 'url';

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}
