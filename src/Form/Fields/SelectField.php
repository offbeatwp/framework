<?php
namespace OffbeatWP\Form\Fields;

class SelectField extends AbstractField
{
    public const FIELD_TYPE = 'select';

    /** @var array<string, string> */
    private array $options = [];

    /**
     * @param array<string, string> $options
     * @return $this
     */
    final public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /** @return $this */
    final public function addOption(string $key, string $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    /** @return array<string, string> */
    final public function getOptions(): array
    {
        return $this->options;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}