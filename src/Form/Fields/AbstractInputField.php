<?php

namespace OffbeatWP\Form\Fields;

use OffbeatWP\Form\Shared\ConditionalLogicTrait;

abstract class AbstractInputField implements FieldInterface
{
    use ConditionalLogicTrait;

    public $id;
    public $label;
    public $attributes = [];

    protected function __construct(?string $name, string $label)
    {
        $this->id = $name;
        $this->label = $label;
    }

    public static function make(?string $name, string $label): AbstractInputField
    {
        return new static($name, $label);
    }

    /* Basic Setters */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /** @internal Use required instead */
    public function setRequired(bool $required = true): void
    {
        $this->setAttribute('required', $required);
    }

    /** @internal Use attribute instead */
    public function setAttribute($key, $value = null): void
    {
        $this->attributes[$key] = $value;
    }

    /** @internal Use attributes instead */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    /* Basic Getters */
    public function getType(): string
    {
        return 'field';
    }

    public function getFieldType(): string
    {
        return static::FIELD_TYPE;
    }

    public function getId(): string
    {
        if (empty($this->id)) {
            $label = $this->getLabel();
            $label = iconv('utf-8', 'ascii//TRANSLIT', $label);
            $label = preg_replace('/[^A-Za-z0-9_-]/', '', $label);
            $label = str_replace(' ', '', $label);
            $label = strtolower($label);

            return $label;
        }

        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getRequired(): bool
    {
        return (bool)$this->getAttribute('required');
    }

    /** @return string[]|bool[] */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute($key)
    {
        return $this->getAttributes()[$key] ?? null;
    }

    /* Chain setters */
    public function description(string $description): AbstractInputField
    {
        $this->setAttribute('description', $description);

        return $this;
    }

    public function default($value): AbstractInputField
    {
        $this->setAttribute('default', $value);

        return $this;
    }

    public function attributes(array $attributes): AbstractInputField
    {
        $this->setAttributes($attributes);

        return $this;
    }

    public function required(bool $required = true): AbstractInputField
    {
        $this->setRequired($required);

        return $this;
    }

    public function attribute(string $key, $value): AbstractInputField
    {
        $this->setAttribute($key, $value);

        return $this;
    }

    /* Functions */
    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'field_type' => $this->getFieldType(),
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'attributes' => $this->getAttributes(),
            'required' => $this->getRequired(),
        ];
    }
}
