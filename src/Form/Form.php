<?php
namespace OffbeatWP\Form;

use Illuminate\Support\Collection;
use OffbeatWP\Form\Fields\AbstractField;
use OffbeatWP\Form\FieldsCollections\FieldsCollectionInterface;
use OffbeatWP\Form\FieldsContainers\AbstractFieldsContainer;
use OffbeatWP\Form\FieldsContainers\FieldsContainerInterface;
use OffbeatWP\Form\FieldsContainers\Repeater;
use OffbeatWP\Form\FieldsContainers\Section;
use OffbeatWP\Form\FieldsContainers\Tab;
use OffbeatWP\Form\Fields\FieldInterface;

final class Form extends Collection
{
    /** @var Form|FieldsContainerInterface */
    private $activeItem;
    private $fieldKeys = [];
    private $fieldPrefix = '';
    public $parent;

    public function __construct()
    {
        parent::__construct();
        $this->activeItem = $this;
    }

    /**
     * @param FieldInterface|FieldsContainerInterface|FieldsCollectionInterface $item
     * @param bool $prepend
     */
    public function add($item, $prepend = false)
    {
        // If item is Tab and active itme is Section move back to parent
        if ($this->getActiveItem() !== $this && $item instanceof FieldsContainerInterface) {
            while ($item::LEVEL < $this->getActiveItem()::LEVEL) {
                $this->closeField();
            }
        }

        // If item is of the same type as the active item move back to parent
        $itemClass = get_class($item);
        if ($itemClass === get_class($this->getActiveItem()) && $itemClass !== self::class) {
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

    /**
     * @param FieldsContainerInterface|Form $item
     * @param bool $setParent
     * @return FieldsContainerInterface|Form
     */
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
        $this->fieldKeys[] = $field->getId();
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

    public function closeField()
    {
        $parentField = $this->getActiveItem()->getParent();

        if ($parentField) {
            $this->setActiveItem($parentField);
        }

        return $this;
    }

    public function getType()
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

    public function toArray()
    {
        $items = $this->map(function ($item) {
            return $item->toArray();
        });

        return $items->toArray();
    }

    public function getFieldKeys()
    {
        return $this->fieldKeys;
    }

    public function addComponentForm($component, $fieldPrefix) {
        $activeItem = $this->getActiveItem();

        if (!is_object($componentForm = $component::getForm())) {
            return;
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

    public function getDefaultValues(): array
    {
        $values = [];

        foreach ($this->items as $item) {
            if ($item instanceof AbstractField || $item instanceof AbstractFieldsContainer) {
                $values[$item->getId()] = $item->getAttribute('default');
            }
        }

        return $values;
    }

    public static function create()
    {
        return new static();
    }
}
