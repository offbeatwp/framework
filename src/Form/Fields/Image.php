<?php

namespace OffbeatWP\Form\Fields;

class Image extends AbstractField
{
    public const FIELD_TYPE = 'image';

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}
