<?php
namespace OffbeatWP\Form\FieldsContainers;

use ArrayIterator;
use IteratorAggregate;
use OffbeatWP\Form\Fields\AbstractField;
use OffbeatWP\Form\FieldsCollections\AbstractFieldsCollection;
use OffbeatWP\Form\Form;

abstract class AbstractFieldsContainer implements FieldsContainerInterface, IteratorAggregate
{
    public const TYPE = '';
    public const LEVEL = 0;

    private string $id;
    private string $label;
    private Form|FieldsContainerInterface|null $parent = null;
    private array $attributes = [];
    private array $items = [];

    final public function __construct(string $id, string $label)
    {
        $this->id = $id;
        $this->label = $label;
    }

    final public function setId(string $id): void
    {
        $this->id = $id;
    }

    final public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    final public function getType(): string
    {
        return static::TYPE;
    }

    final public function getId(): string
    {
        return $this->id;
    }

    final public function getLabel(): string
    {
        return $this->label;
    }

    final public function setParent(Form|FieldsContainerInterface $item): void
    {
        $this->parent = $item;
    }

    final public function getParent(): FieldsContainerInterface|Form|null
    {
        return $this->parent;
    }

    /** @param mixed[] $attributes */
    final public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /** @return mixed[] */
    final public function getAttributes(): array
    {
        return $this->attributes;
    }

    final public function getAttribute(string $key): mixed
    {
        return $this->getAttributes()[$key] ?? null;
    }

    final public function add(AbstractField|AbstractFieldsContainer|AbstractFieldsCollection|Form $item): void
    {
        $this->items[] = $item;
    }

    /** @return array{type: string, id: string, label: string, attributes: mixed[], items: mixed[]} */
    final public function toArray()
    {
        return [
            'type'       => $this->getType(),
            'id'         => $this->getId(),
            'label'      => $this->getLabel(),
            'attributes' => $this->getAttributes(),
            'items'      => $this->items,
        ];
    }

    /**
     * Expects a multi-dimensional array as argument.
     * Arrays should have a <b>field</b>, <b>operator</b> and optionally a <b>value</b> key
     * @param array{field: string, operator: string, value?: string|int}[][] $logic
     * @return $this
     */
    final public function conditionalLogic(array $logic)
    {
        $this->attributes['conditional_logic'] = $logic;
        return $this;
    }

    final public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}
