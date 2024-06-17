<?php

namespace OffbeatWP\Form\Fields;

class Checkbox extends AbstractField
{
    public const FIELD_TYPE = 'checkbox';

    /** @var array<string|int, scalar|null> */
    public $options = [];

    /**
     * @param array<string|int, scalar|null> $options
     * @return $this
     */
    public function addOptions($options = [])
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param string|int $key
     * @param scalar|null $value
     * @return $this
     */
    public function addOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    /** @return array<string|int, scalar|null> */
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
