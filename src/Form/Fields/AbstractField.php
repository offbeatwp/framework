<?php

namespace OffbeatWP\Form\Fields;

abstract class AbstractField
{
    public const FIELD_TYPE = '';

    /** @var mixed[] */
    private array $attributes = [];
    private readonly string $id;
    private string $label;
    private bool $required;

    final public function __construct(string $id)
    {
        $this->id = $id;
        $this->init();
    }

    /** This method is executed when the field is constructed */
    protected function init(): void
    {
        // Only exists to be overriden
    }

    final public static function make(string $id, string $label): static
    {
        $field = new static($id);
        $field->label = $label;

        return $field;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /** @internal Use required instead */
    public function setRequired(bool $required = true): void
    {
        $this->required = $required;
        $this->setAttribute('required', $required);
    }

    /** @internal Use attribute instead */
    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * @internal Use attributes instead
     * @param mixed[] $attributes
     */
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
        if (!$this->id) {
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
        return $this->required;
    }

    /** @return mixed[] */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $key): mixed
    {
        return $this->getAttributes()[$key] ?? null;
    }

    /* Chain setters */
    public function description(string $description): self
    {
        $this->setAttribute('description', $description);
        return $this;
    }

    /** @return $this */
    final public function default(mixed $value)
    {
        $this->setAttribute('default', $value);
        return $this;
    }

    /**
     * @param mixed[] $attributes
     * @return $this
     */
    public function attributes(array $attributes)
    {
        $this->setAttributes($attributes);
        return $this;
    }

    /** @return $this */
    public function required(bool $required = true)
    {
        $this->setRequired($required);
        return $this;
    }

    /** @return $this */
    public function allowNull(bool $allowNull = true)
    {
        $this->setAttribute('allow_null', ($allowNull) ? 1 : 0);
        return $this;
    }

    /**
     * Expects a multi-dimensional array as argument.
     * Arrays should have a <b>field</b>, <b>operator</b> and optionally a <b>value</b> key
     * @param array{field: string, operator: string, value?: string|int}[][] $logic
     * @return $this
     */
    public function conditionalLogic(array $logic): self
    {
        $this->setAttribute('conditional_logic', $logic);
        return $this;
    }

    /** @return $this */
    public function attribute(string $key, mixed $value): self
    {
        $this->setAttribute($key, $value);
        return $this;
    }

    /** @return $this */
    public function width(int $percent)
    {
        $this->setAttribute('width', $percent);
        return $this;
    }

    /** @return array{type: string, field_type: string, id: string, label: string, attributes: mixed[], required: bool} */
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
