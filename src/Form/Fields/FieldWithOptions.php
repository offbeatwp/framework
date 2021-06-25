<?php

namespace OffbeatWP\Form\Fields;

abstract class FieldWithOptions extends AbstractField
{
    /** @var string[]|callable */
    public $options = [];

    /**
     * @param string[]|callable $options
     * @return $this
     */
    public function addOptions($options = []): FieldWithOptions
    {
        $this->options = $options;

        return $this;
    }

    public function addOption(string $value, string $label): FieldWithOptions
    {
        $this->options[$value] = $label;

        return $this;
    }

    /** @return string[] */
    public function getOptions(): array
    {
        if (is_callable($this->options)) {
            return call_user_func($this->options);
        }

        return $this->options;
    }
}