<?php
namespace OffbeatWP\Form\Fields;

final class ButtonGroupField extends AbstractField
{
    public const FIELD_TYPE = 'button_group';

    /** @var array<string, string> */
    public array $options = [];

    /**
     * @param array<string, string> $options
     * @return $this
     */
    public function addOptions(array $options = [])
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

    /** @return array<string, string> */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getFieldType(): string
    {
        return self::FIELD_TYPE;
    }
}