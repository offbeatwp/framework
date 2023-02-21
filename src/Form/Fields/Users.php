<?php
namespace OffbeatWP\Form\Fields;

class Users extends AbstractField {
    public const FIELD_TYPE = 'users';

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}