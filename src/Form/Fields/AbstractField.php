<?php

namespace OffbeatWP\Form\Fields;

abstract class AbstractField
{
    protected string $id;
    protected string $label;
    protected array $attributes = [];

    final public function __construct(string $id, string $label)
    {
        $this->id = $id;
        $this->label = $label;
        $this->init();
    }

    protected function init(): void
    {
        // Only exists to be overriden
    }

    /**
     * @param non-empty-string $id
     * @param string $label
     * @return static
     */
    public static function make(string $id, string $label)
    {
        return new static($id, $label);
    }

    /* Basic Setters */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function setRequired(bool $required = true): self
    {
        $this->setAttribute('required', $required);
        return $this;
    }

    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /* Basic Getters */
    public function getType(): string
    {
        return 'field';
    }

    abstract public function getFieldType(): string;

    public function getId(): string
    {
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

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $key)
    {
        return $this->getAttributes()[$key] ?? null;
    }

    /* Chain setters */
    public function description(string $description): self
    {
        $this->setAttribute('description', $description);
        return $this;
    }

    public function default($value): self
    {
        $this->setAttribute('default', $value);
        return $this;
    }

    public function attributes(array $attributes): self
    {
        $this->setAttributes($attributes);
        return $this;
    }

    public function required(bool $required = true): self
    {
        $this->setRequired($required);
        return $this;
    }

    public function allowNull(bool $allowNull = true): self
    {
        $this->setAttribute('allow_null', ($allowNull) ? 1 : 0);
        return $this;
    }

    public function attribute(string $key, $value): self
    {
        $this->setAttribute($key, $value);
        return $this;
    }

    public function width(int $percent): self
    {
        $this->setAttribute('width', $percent);
        return $this;
    }

    public function conditionalLogic(array $conditionalLogic): self
    {
        $this->setAttribute('conditional_logic', $conditionalLogic);
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
