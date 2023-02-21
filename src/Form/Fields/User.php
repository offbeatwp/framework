<?php
namespace OffbeatWP\Form\Fields;

class User extends AbstractField {
    public const FIELD_TYPE = 'user';

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}