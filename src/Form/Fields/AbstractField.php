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
    /** @var mixed[] */
    public $attributes = [];

    public function __construct()
    {
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

    /**
     * @internal Use attribute instead
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setAttribute($key, $value = null): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * @internal Use attributes instead
     * @param mixed[] $attributes
     * @return void
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

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getAttribute($key)
    {
        return $this->getAttributes()[$key] ?? null;
    }

    /* Chain setters */
    /** @return $this */
    public function description(string $description)
    {
        $this->setAttribute('description', $description);
        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function default($value)
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
    public function conditionalLogic(array $logic)
    {
        $this->setAttribute('conditional_logic', $logic);
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function attribute(string $key, $value)
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
