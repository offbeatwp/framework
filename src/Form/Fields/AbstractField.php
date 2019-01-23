<?php
namespace OffbeatWP\Form\Fields;

class AbstractField implements FieldInterface
{
    public $id;
    public $label;
    public $attributes = [];

    public static function make($id, $label)
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

    public function getType()
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

    public function setAttribute($key, $value = null)
    {
        $this->attributes[$key] = $value;
    }

    public function setAttributes($attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute($key)
    {
        if (isset($this->getAttributes()[$key])) {
            return $this->getAttributes()[$key];
        }
        return false;
    }

    /* Chain setters */

    public function description($description)
    {
        $this->setAttribute('description', $description);

        return $this;
    }

    function default($value) {
        $this->setAttribute('default', $value);

        return $this;
    }

    public function attributes($attributes)
    {
        $this->setAttributes($attributes);

        return $this;
    }

    public function attribute($key, $value)
    {
        $this->setAttribute($key, $value);

        return $this;
    }

    /* Functional */

    public function toArray()
    {
        return [
            'type'       => 'field',
            'field_type' => $this->getFieldType(),
            'id'         => $this->getId(),
            'label'      => $this->getLabel(),
            'attributes' => $this->getAttributes(),
        ];
    }
}
