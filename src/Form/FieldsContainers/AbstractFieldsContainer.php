<?php

namespace OffbeatWP\Form\FieldsContainers;

use Illuminate\Support\Collection;

class AbstractFieldsContainer extends Collection implements FieldsContainerInterface
{
    const LEVEL = -1;

    public $id;
    public $label;
    public $parent;
    public $attributes = [];

    public function __construct(string $id, string $label)
    {
        $this->setLabel($label);
        $this->setId($id);
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function setLabel(string $label)
    {
        $this->label = $label;
    }

    public function getType()
    {
        return static::TYPE;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLabel()
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
        return false;
    }

    public function add($item)
    {
        $this->push($item);

        return $item;
    }

    public function toArray(): array
    {
        $items = $this->map(function ($item) {
            return $item->toArray();
        });

        return [
            'type' => $this->getType(),
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'attributes' => $this->getAttributes(),
            'items' => $items->toArray()
        ];
    }

    public static function getLevel(): int
    {
        return self::LEVEL;
    }
}
