<?php
namespace OffbeatWP\Form\Fields;

final class RadioField extends AbstractField {
    public const FIELD_TYPE = 'radio';

    /** @var string[] */
    public array $options = [];

    /**
     * @param string[] $options
     * @return $this
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options;
        return $this;
    }

    /** @return $this */
    public function addOption(string $key, string $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /** @return string[] */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}