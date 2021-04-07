<?php
namespace OffbeatWP\Form;

use Illuminate\Support\Collection;
use OffbeatWP\Form\FieldsCollections\AbstractFieldsCollection;
use OffbeatWP\Form\FieldsContainers\FieldsContainerInterface;
use OffbeatWP\Form\FieldsContainers\Repeater;
use OffbeatWP\Form\FieldsContainers\Section;
use OffbeatWP\Form\FieldsContainers\Tab;
use OffbeatWP\Form\Fields\FieldInterface;

class Form extends Collection
{
    private $activeItem;
    private $fieldKeys = [];
    private $fieldPrefix = '';
    public $parent;

    public function __construct()
    {
        $this->activeItem = $this;
    }

    /**
     * @param mixed|Form|FieldsContainerInterface|FieldInterface $item
     * @param bool $prepend
     */
    public function add($item, $prepend = false): Form
    {
        // If item is Tab and active item is Section move back to parent
        if ($this->getActiveItem() != $this && $item instanceof FieldsContainerInterface) {
            while ($item::LEVEL < $this->getActiveItem()::LEVEL) {
                $this->closeField();
            }
        }

        // If item is of the same type as the active item move back to parent
        if (get_class($item) == get_class($this->getActiveItem()) && get_class($item) != self::class) {
            $this->closeField();
        }

        // If active item is the form, push item directly the the items
        if ($this->getActiveItem() === $this) {
            if ($prepend) {
                $this->prepend($item);
            } else {
                $this->push($item);
            }


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

    public function addTab($id, $label): Form
    {
        $this->add(new Tab($id, $label));

        return $this;
    }

    public function addSection($id, $label): Form
    {
        $this->add(new Section($id, $label));

        return $this;
    }

    public function addRepeater($id, $label): Form
    {
        $this->add(new Repeater($id, $label));

        return $this;
    }

    public function addField(FieldInterface $field): Form
    {
        $this->fieldKeys[] = $field->getId();
        $this->add($field);

        return $this;
    }

    public function addFields(AbstractFieldsCollection $fieldsCollection): Form
    {
        $fieldsCollection->each(function ($field) {
            $this->addField($field);
        });

        return $this;
    }

    public function closeField(): Form
    {
        $parentField = $this->getActiveItem()->getParent();

        if (!empty($parentField)) {
            $this->setActiveItem($parentField);
        }

        return $this;
    }

    public function getType(): string
    {
        return 'form';
    }

    public function getFieldPrefix()
    {
        return $this->fieldPrefix;
    }

    public function setFieldPrefix($fieldPrefix)
    {
        $this->fieldPrefix = $fieldPrefix;
    }

    public function toArray(): array
    {
        $items = $this->map(function ($item) {
            return $item->toArray();
        });

        return $items->toArray();
    }

    public function getFieldKeys(): array
    {
        return $this->fieldKeys;
    }

    public function addComponentForm($component, $fieldPrefix) {
        $activeItem = $this->getActiveItem();

        if (!is_object($componentForm = $component::getForm())) {
            return ;
        }

        $form = clone $componentForm;
        $form->setFieldPrefix($fieldPrefix);

        $this->addSection($fieldPrefix, $component::getName())->add($form);

        $this->setActiveItem($activeItem);
    }

    public function setParent($item)
    {
        $this->parent = $item;
    }

    public function getParent()
    {
        return $this->parent;
    }
}
