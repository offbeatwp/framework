<?php
namespace OffbeatWP\Form\FieldsContainers;

use Illuminate\Support\Collection;
use OffbeatWP\Form\Fields\AbstractField;
use OffbeatWP\Form\FieldsCollections\AbstractFieldsCollection;
use OffbeatWP\Form\Form;

class AbstractFieldsContainer extends Collection implements FieldsContainerInterface
{
    /** @var string */
    public $id;
    /** @var string */
    public $label;
    /** @var Form|FieldsContainerInterface */
    public $parent;
    /** @var mixed[] */
    public $attributes = [];

    /**
     * @param string $id
     * @param string $label
     */
    public function __construct($id, $label)
    {
        parent::__construct();
        $this->setLabel($label);
        $this->setId($id);
    }

    /**
     * @param string $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $label
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /** @return string */
    public function getType()
    {
        return static::TYPE;
    }

    /** @return string */
    public function getId()
    {
        return $this->id;
    }

    /** @return string */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param Form|FieldsContainerInterface $item
     * @return $this
     */
    public function setParent($item)
    {
        $this->parent = $item;
        return $this;
    }

    /** @return FieldsContainerInterface|Form */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed[] $attributes
     * @return self
     */
    public function setAttributes($attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /** @return mixed[] */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $key
     * @return false|mixed
     */
    public function getAttribute($key)
    {
        return $this->getAttributes()[$key] ?? false;
    }

    /**
     * @param AbstractField|AbstractFieldsContainer|AbstractFieldsCollection|Form $item
     * @return AbstractField|AbstractFieldsContainer|AbstractFieldsCollection|Form
     */
    public function add($item)
    {
        $this->items[] = $item;
        return $item;
    }

    /** @return array{type: string, id: string, label: string, attributes: mixed[], items: mixed[]} */
    public function toArray()
    {
        $items = $this->map(fn($item) => $item->toArray());

        return [
            'type'       => $this->getType(),
            'id'         => $this->getId(),
            'label'      => $this->getLabel(),
            'attributes' => $this->getAttributes(),
            'items'      => $items->toArray(),
        ];
    }

    /**
     * Expects a multi-dimensional array as argument.
     * Arrays should have a <b>field</b>, <b>operator</b> and optionally a <b>value</b> key
     * @param array{field: string, operator: string, value?: string|int}[][] $logic
     * @return $this
     */
    public function conditionalLogic(array $logic): self
    {
        $this->attributes['conditional_logic'] = $logic;
        return $this;
    }
}
