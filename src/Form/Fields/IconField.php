<?php

namespace OffbeatWP\Form\Fields;

class IconField extends ButtonGroup {
    public function __construct() {
        $this->setAttribute('class', 'offbeat-icon-field');
        $this->setAttribute('allow_null', true);
    }
}