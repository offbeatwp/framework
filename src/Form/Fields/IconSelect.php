<?php

namespace OffbeatWP\Form\Fields;

class IconSelect extends Select {
    public function __construct() {
        $this->setAttribute('class', 'offbeat-icon-field');
        $this->setAttribute('allow_null', true);
    }
}