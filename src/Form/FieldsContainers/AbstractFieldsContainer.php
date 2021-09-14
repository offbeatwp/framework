<?php
namespace OffbeatWP\Form\FieldsContainers;

use Illuminate\Support\Collection;
use OffbeatWP\Form\IFormSection;

class AbstractFieldsContainer extends Collection implements FieldsContainerInterface, IFormSection
{
    public $id;
    public $label;
    public $parent;
    public $attributes = [];

    public function __construct($id, $label)
    {
        parent::__construct();
        $this->setLabel($label);
        $this->setId($id);
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
        return static::TYPE;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setParent($item)
    {
        $this->parent = $item;
    }

    public function getParent()
    {
        return $this->parent;
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

    public function add($item)
    {
        $this->push($item);

        return $item;
    }

    public function toArray()
    {
        $items = $this->map(function ($item) {
            return $item->toArray();
        });

        return [
            'type'       => $this->getType(),
            'id'         => $this->getId(),
            'label'      => $this->getLabel(),
            'attributes' => $this->getAttributes(),
            'items'      => $items->toArray(),
        ];
    }
}
