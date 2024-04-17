<?php
namespace OffbeatWP\Form\Fields;

class DateTimePicker extends AbstractField {
    public const FIELD_TYPE = 'date_time_picker';

    protected function __construct() {
        parent::__construct();

        $this->setAttribute('display_format', 'd/m/Y H:i:s');
        $this->setAttribute('return_format', 'd/m/Y H:i:s');
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}