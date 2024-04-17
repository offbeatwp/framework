<?php
namespace OffbeatWP\Form\Fields;

final class RadioField extends AbstractField {
    public const FIELD_TYPE = 'radio';

    public array $options = [];

    public function setOptions(array $options = [])
    {
        $this->options = $options;
        return $this;
    }

    public function addOption(int|string $key, mixed $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}