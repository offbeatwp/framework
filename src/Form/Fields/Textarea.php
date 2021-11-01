<?php
namespace OffbeatWP\Form\Fields;

class Textarea extends AbstractField {
    public const FIELD_TYPE = 'textarea';

    public function __construct() {
        $this->setAttribute('new_lines', 'br');
    }
}
