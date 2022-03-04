<?php
namespace OffbeatWP\Form\Fields;

class Textarea extends AbstractField {
    public const FIELD_TYPE = 'textarea';

    public function __construct (string $id, string $label = '') {
        parent::__construct($id, $label);
        $this->setAttribute('new_lines', 'br');
    }
}
