<?php
namespace OffbeatWP\Form\Fields;

class Radio extends AbstractInputField {
    public const FIELD_TYPE = 'radio';

    public $options = [];

    public function addOptions($options = []): Radio
    {
        $this->options = $options;

        return $this;
    }

    public function addOption($key, $value): Radio
    {
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