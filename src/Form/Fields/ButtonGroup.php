<?php

namespace OffbeatWP\Form\Fields;

class ButtonGroup extends AbstractField
{
    public const FIELD_TYPE = 'button_group';

    public $options = [];

    public function addOptions($options = [])
    {
        $this->options = $options;

        return $this;
    }

    public function addOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    public function getOptions()
    {
        if (is_callable($this->options)) {
            return call_user_func($this->options);
        }
        return $this->options;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}
