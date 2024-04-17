<?php
namespace OffbeatWP\Form\Fields;

class SelectField extends AbstractField
{
    public const FIELD_TYPE = 'select';

    private array $options = [];

    final public function setOptions(array|callable $options = [])
    {
        $this->options = $options;

        return $this;
    }

    final public function addOption(string|int $key, mixed $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    final public function getOptions(): array
    {
        return $this->options;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}