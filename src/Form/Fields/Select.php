<?php
namespace OffbeatWP\Form\Fields;

class Select extends AbstractField {
    const FIELD_TYPE = 'select';

    public $options = [];

    public function addOptions($options = []) {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    public function addOption($key, $value) {
        $this->options[$key] = $value;

        return $this;
    }

    public function getOptions() {
        if (is_callable($this->options)) {
            return call_user_func($this->options);
        }
        return $this->options;
    }
}