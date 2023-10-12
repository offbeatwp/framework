<?php
namespace OffbeatWP\Form\Fields;

class TimePicker extends AbstractField {
    public const FIELD_TYPE = 'time_picker';

    public function __construct () {
        parent::__construct();

        $this->setAttribute('display_format', 'H:i:s');
        $this->setAttribute('return_format', 'H:i:s');
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}