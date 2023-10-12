<?php
namespace OffbeatWP\Form\Fields;

class DatePicker extends AbstractField {
    public const FIELD_TYPE = 'date_picker';

    public function __construct () {
        $this->setAttribute('display_format', 'd/m/Y');
        $this->setAttribute('return_format', 'd/m/Y');
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}