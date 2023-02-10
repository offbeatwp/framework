<?php
namespace OffbeatWP\Form\Fields;

class ButtonGroup extends AbstractField {
    public const FIELD_TYPE = 'button_group';

    public $options = [];

    /** @deprecated Misleading name as this method actually REPLACES all options */
    public function addOptions($options = []) {
        $this->options = $options;
        return $this;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function addOption(string $key, $value): self
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