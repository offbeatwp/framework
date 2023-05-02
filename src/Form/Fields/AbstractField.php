<?php

namespace OffbeatWP\Form\Fields;

abstract class AbstractField implements FieldInterface
{
    /** @var string */
    public $id;
    /** @var string */
    public $label;
    /** @var bool */
    public $required;
    /** @var string[]|bool[] */
    public $attributes = [];

    public function __construct() {
        $this->init();
    }

    protected function init(): void
    {
        // Only exists to be overriden
    }

    /**
     * @param string $id
     * @param string $label
     * @return static
     */
    public static function make(string $id, string $label)
    {
        $field = new static();

        $field->setId($id);
        $field->setLabel($label);

        return $field;
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
        $this->required = $required;
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

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute($key)
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

    /**
     * @param array{field: string, operator: string, value?: string}[] $logic
     * @return $this
     */
    public function conditionalLogic(array $logic): self
    {
        $this->setAttribute('conditional_logic', $logic);
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
