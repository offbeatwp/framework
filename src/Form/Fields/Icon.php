<?php

namespace OffbeatWP\Form\Fields;

class Icon extends Select {
    public function __construct() {
        $this->setAttribute('class', 'iconfield');
        $this->setAttribute('allow_null', true);
    }
}