<?php
namespace OffbeatWP\Form\Fields;

final class TimePickerField extends AbstractField {
    public const FIELD_TYPE = 'time_picker';

    protected function __construct () {
        parent::__construct();

        $this->setAttribute('display_format', 'H:i:s');
        $this->setAttribute('return_format', 'H:i:s');
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}