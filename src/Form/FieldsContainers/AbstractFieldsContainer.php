<?php
namespace OffbeatWP\Form\FieldsContainers;

use Illuminate\Support\Collection;
use OffbeatWP\Form\Fields\FieldInterface;
use OffbeatWP\Form\FieldsCollections\FieldsCollectionInterface;
use OffbeatWP\Form\IFormSection;

class AbstractFieldsContainer extends Collection implements FieldsContainerInterface, IFormSection
{
    public const LEVEL = 0;

    public $id;
    public $label;
    public $parent;
    public $attributes = [];

    public function __construct(string $id, string $label)
    {
        parent::__construct();
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

    public function getType(): string
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

    public function setParent($item): void
    {
        $this->parent = $item;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getLevel(): int
    {
        return self::LEVEL;
    }

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
        return $this->getAttributes()[$key] ?? false;
    }

    /**
     * @param FieldInterface|FieldsContainerInterface|FieldsCollectionInterface $item
     * @return FieldInterface|FieldsContainerInterface|FieldsCollectionInterface
     */
    public function add($item)
    {
        $this->push($item);

        return $item;
    }

    /** @return array{type: mixed, id: string, label: string, attributes: array, items: array} */
    public function toArray(): array
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
