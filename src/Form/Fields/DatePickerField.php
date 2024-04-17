<?php
namespace OffbeatWP\Form\Fields;

final class DatePickerField extends AbstractField
{
    public const FIELD_TYPE = 'date_picker';

    protected function init(): void
    {
        $this->setAttribute('display_format', 'd/m/Y');
        $this->setAttribute('return_format', 'd/m/Y');
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}