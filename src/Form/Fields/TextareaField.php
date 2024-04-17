<?php
namespace OffbeatWP\Form\Fields;

final class TextareaField extends AbstractField {
    public const FIELD_TYPE = 'textarea';

    protected function init(): void
    {
        $this->setAttribute('new_lines', 'br');
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}
