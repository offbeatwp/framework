<?php
namespace OffbeatWP\Form\Fields;

class ButtonGroup extends AbstractInputField {
    public const FIELD_TYPE = 'button_group';

    public $options = [];

    public function addOptions($options = []): ButtonGroup
    {
        $this->options = $options;

        return $this;
    }

    public function addOption($key, $value): ButtonGroup
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