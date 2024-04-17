<?php
namespace OffbeatWP\Form\Fields;

final class UserField extends AbstractField
{
    public const FIELD_TYPE = 'user';

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}