<?php
namespace OffbeatWP\Form;

use Illuminate\Support\Collection;
use OffbeatWP\Form\FieldsCollections\FieldsCollectionInterface;
use OffbeatWP\Form\FieldsContainers\FieldsContainerInterface;
use OffbeatWP\Form\FieldsContainers\Repeater;
use OffbeatWP\Form\FieldsContainers\Section;
use OffbeatWP\Form\FieldsContainers\Tab;
use OffbeatWP\Form\Fields\FieldInterface;

class Form extends Collection
{
    private $activeItem;

    public function __construct()
    {
        $this->activeItem = $this;
    }

    private function add($item)
    {
        // If item is Tab and active itme is Section move back to parent

        if ($this->getActiveItem() != $this && $item instanceof FieldsContainerInterface) {
            while ($item::LEVEL < $this->getActiveItem()::LEVEL) {
                $this->setActiveItem($this->getActiveItem()->getParent());
            }
        }

        // If item is of the same type as the active item move back to parent
        if (get_class($item) == get_class($this->getActiveItem())) {
            $this->setActiveItem($this->getActiveItem()->getParent());
        }

        // If active item is the form, push item directly the the items
        if ($this->getActiveItem() === $this) {
            $this->push($item);

            if ($item instanceof FieldsContainerInterface) {
                $this->setActiveItem($item, true);
            }

            return $this;
        }

        // Add item to current active item
        $this->getActiveItem()->add($item);

        // If item is instance of Fields Container, change the active item.
        if ($item instanceof FieldsContainerInterface) {
            $this->setActiveItem($item, true);
        }

        return $this;
    }

    public function getActiveItem()
    {
        return $this->activeItem;
    }

    public function setActiveItem($item, $setParent = false)
    {
        if ($setParent) {
            $item->setParent($this->getActiveItem());
        }

        $this->activeItem = $item;

        return $this->activeItem;
    }

    public function addTab($id, $label)
    {
        $this->add(new Tab($id, $label));

        return $this;
    }

    public function addSection($id, $label)
    {
        $this->add(new Section($id, $label));

        return $this;
    }

    public function addRepeater($id, $label)
    {
        $this->add(new Repeater($id, $label));

        return $this;
    }

    public function addField(FieldInterface $field)
    {
        $this->add($field);

        return $this;
    }

    public function addFields(FieldsCollectionInterface $fieldsCollection)
    {
        $fieldsCollection->each(function ($field) {
            $this->addField($field);
        });

        return $this;
    }

    public function getType()
    {
        return 'form';
    }

    public function toArray()
    {
        $items = $this->map(function ($item) {
            return $item->toArray();
        });

        return $items->toArray();
    }
}
