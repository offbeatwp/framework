<?php
namespace OffbeatWP\Form\FieldsContainers;

use OffbeatWP\Form\FieldsContainer;

abstract class AbstractFieldsContainer extends FieldsContainer
{
    protected string $id;
    protected string $label;
    protected array $attributes = [];

    public function __construct(string $id, string $label)
    {
        parent::__construct();
        $this->id = $id;
        $this->label = $label;
    }

    public function getLevel(): int
    {
        return 1;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    abstract public function getType(): string;

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $key)
    {
        return $this->getAttributes()[$key] ?? null;
    }

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
