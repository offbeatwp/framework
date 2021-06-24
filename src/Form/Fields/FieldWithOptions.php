<?php

namespace OffbeatWP\Form\Fields;

abstract class FieldWithOptions extends AbstractField
{
    /** @var string[]|int[]|callable */
    public $options = [];

    /**
     * @param string[]|int[]|callable $options
     * @return $this
     */
    public function addOptions($options = []): FieldWithOptions
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param string|int $value
     * @param string $label
     * @return $this
     */
    public function addOption($value, string $label): FieldWithOptions
    {
        $this->options[$value] = $label;

        return $this;
    }

    /** @return string[]|int[] */
    public function getOptions(): array
    {
        if (is_callable($this->options)) {
            return call_user_func($this->options);
        }

        return $this->options;
    }
}