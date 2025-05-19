<?php

namespace OffbeatWP\Form\Fields;

class Select extends AbstractField
{
    public const FIELD_TYPE = 'select';

    /** @var array<string|int, scalar|null> */
    public $options = [];

    /**
     * @param array<int|string, scalar|null>|array{0: Select, 1: non-falsy-string} $options
     * @return $this
     */
    public function addOptions(array $options = [])
    {
        $this->options = array_replace($this->options, $options);

        return $this;
    }

    /**
     * @param int|string $key
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
