<?php
namespace OffbeatWP\Form\Fields;

final class PostTypeField extends AbstractField {
    public const FIELD_TYPE = 'post_type';

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}