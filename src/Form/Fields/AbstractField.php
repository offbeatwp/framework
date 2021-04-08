<?php

namespace OffbeatWP\Form\Fields;

class AbstractField implements FieldInterface
{
    public $id;
    public $label;
    public $required;
    public $attributes = [];

    public static function make($id, $label): AbstractField
    {
        $field = new static();

        $field->setId($id);
        $field->setLabel($label);

        if (!empty($attr)) {
            $field->setAttributes($attr);
        }

        return $field;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function setRequired($required)
    {
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

    public function getId()
    {
        if (!isset($this->id) || empty($this->id)) {
            $label = $this->getLabel();
            $label = iconv('utf-8', 'ascii//TRANSLIT', $label);
            $label = preg_replace('/[^A-Za-z0-9_-]/', '', $label);
            $label = str_replace(' ', '', $label);
            $label = strtolower($label);

            return $label;
        }

        return $this->id;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getRequired()
    {
        return $this->required;
    }

    /** @internal Use attribute instead */
    public function setAttribute($key, $value = null)
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

    public function getAttribute($key)
    {
        if (isset($this->getAttributes()[$key])) {
            return $this->getAttributes()[$key];
        }

        return null;
    }

    /* Chain setters */
    public function description($description): AbstractField
    {
        $this->setAttribute('description', $description);

        return $this;
    }

    function default($value): AbstractField
    {
        $this->setAttribute('default', $value);

        return $this;
    }

    public function attributes(array $attributes): AbstractField
    {
        $this->setAttributes($attributes);

        return $this;
    }

    public function attribute($key, $value): AbstractField
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
