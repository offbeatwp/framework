<?php

namespace OffbeatWP\Form\Fields;

class Link extends AbstractField
{
    public const FIELD_TYPE = 'link';

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}
