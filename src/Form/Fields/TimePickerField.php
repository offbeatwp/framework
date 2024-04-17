<?php
namespace OffbeatWP\Form\Fields;

final class TimePickerField extends AbstractField {
    public const FIELD_TYPE = 'time_picker';

    protected function init(): void
    {
        $this->setAttribute('display_format', 'H:i:s');
        $this->setAttribute('return_format', 'H:i:s');
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}