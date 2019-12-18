<?php
namespace OffbeatWP\Form\Fields;

class TextArea extends AbstractField {
    const FIELD_TYPE = 'textarea';

    public function __construct () {
        $this->setAttribute('new_lines', 'br');
    }
}