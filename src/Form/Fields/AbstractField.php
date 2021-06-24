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
    /** @var array */
    public $attributes = [];

    /**
     * @noinspection PhpMissingReturnTypeInspection
     * @return $this
     */
    public static function make(string $id, string $label)
    {
        $field = new static();

        $field->setId($id);
        $field->setLabel($label);

        if (!empty($attr)) {
            $field->setAttributes($attr);
        }

        return $field;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function setLabel(string $label)
    {
        $this->label = $label;
    }

    public function setRequired(bool $required = true)
    {
        $this->attribute('required', $required);
        $this->required = $required;
    }

    public function getType(): string
    {
        return 'field';
    }

    public function getFieldType()
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
        return $this->required;
    }

    /** @internal Use attribute instead */
    public function setAttribute(string $key, $value = null)
    {
        $this->attributes[$key] = $value;
    }

    /** @internal Use attributes instead */
    public function setAttributes(array $attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $key)
    {
        if (isset($this->getAttributes()[$key])) {
            return $this->getAttributes()[$key];
        }

        return null;
    }

    /* Chain setters */
    public function description(string $description): AbstractField
    {
        $this->setAttribute('description', $description);

        return $this;
    }

    function default($value): AbstractField
    {
        $this->setAttribute('default', $value);

        return $this;
    }

    /**
     * @param string[]|int[]|float[] $attributes
     * @return $this
     */
    public function attributes(array $attributes): AbstractField
    {
        $this->setAttributes($attributes);

        return $this;
    }

    /**
     * @param string $key
     * @param string|int|float $value
     * @return $this
     */
    public function attribute(string $key, $value): AbstractField
    {
        $this->setAttribute($key, $value);

        return $this;
    }

    /* Functional */
    public function toArray(): array
    {
        return [
            'type' => 'field',
            'field_type' => $this->getFieldType(),
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'attributes' => $this->getAttributes(),
            'required' => $this->getRequired(),
        ];
    }
}
