<?php
namespace OffbeatWP\Form\Fields;

final class UsersField extends AbstractField
{
    public const FIELD_TYPE = 'users';

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}