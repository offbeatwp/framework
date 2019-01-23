<?php
namespace OffbeatWP\Form\Fields;

class Select extends AbstractField {
    const FIELD_TYPE = 'select';

    public $options = [];

    public function options($options = []) {
        $this->options = $options;

        return $this;
    }

    public function getOptions() {
        if (is_callable($this->options)) {
            return call_user_func($this->options);
        }
        return $this->options;
    }
}