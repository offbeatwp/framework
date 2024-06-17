<?php

namespace OffbeatWP\Form;

use Illuminate\Support\Collection;
use OffbeatWP\Components\AbstractComponent;
use OffbeatWP\Form\Fields\AbstractField;
use OffbeatWP\Form\FieldsCollections\AbstractFieldsCollection;
use OffbeatWP\Form\FieldsContainers\AbstractFieldsContainer;
use OffbeatWP\Form\FieldsContainers\Repeater;
use OffbeatWP\Form\FieldsContainers\Section;
use OffbeatWP\Form\FieldsContainers\Tab;

/** @extends Collection<int, AbstractField|AbstractFieldsCollection|AbstractFieldsContainer|Form> */
final class Form extends Collection
{
    /** @var string[] */
    private array $fieldKeys = [];
    private string $fieldPrefix = '';
    private AbstractFieldsContainer|Form $activeItem;
    public Form|AbstractFieldsContainer|null $parent = null;

    public function __construct()
    {
        parent::__construct();
        $this->activeItem = $this;
    }

    /**
     * @param AbstractField|AbstractFieldsContainer|AbstractFieldsCollection|Form $item
     * @param bool $prepend
     */
    public function add($item, $prepend = false)
    {
        // If item is Tab and active item is Section move back to parent
        if ($this->getActiveItem() !== $this && $item instanceof AbstractFieldsContainer) {
            while ($item::LEVEL < $this->getActiveItem()::LEVEL) {
                $this->closeField();
            }
        }

        // If item is of the same type as the active item move back to parent
        $itemClass = $item::class;
        if ($itemClass === $this->getActiveItem()::class && $itemClass !== self::class) {
            $this->closeField();
        }

        // If active item is the form, push item directly into the collection
        if ($this->getActiveItem() === $this) {
            if ($prepend) {
                $this->prepend($item);
            } else {
                $this->push($item);
            }

            if ($item instanceof AbstractFieldsContainer) {
                $this->setActiveItem($item, true);
            }

            return $this;
        }

        // Add item to current active item
        $this->getActiveItem()->add($item);

        // If item is instance of Fields Container, change the active item.
        if ($item instanceof AbstractFieldsContainer) {
            $this->setActiveItem($item, true);
        }

        return $this;
    }

    /** @return Form|AbstractFieldsContainer */
    public function getActiveItem()
    {
        return $this->activeItem;
    }

    /**
     * @param Form|AbstractFieldsContainer $item
     * @param bool $setParent
     * @return Form|AbstractFieldsContainer
     */
    public function setActiveItem($item, $setParent = false)
    {
        if ($setParent) {
            $item->setParent($this->getActiveItem());
        }

        $this->activeItem = $item;

        return $this->activeItem;
    }

    /**
     * @param string $id
     * @param string $label
     * @return $this
     */
    public function addTab($id, $label)
    {
        $this->add(new Tab($id, $label));

        return $this;
    }

    /**
     * @param string $id
     * @param string $label
     * @return $this
     */
    public function addSection($id, $label)
    {
        $this->add(new Section($id, $label));

        return $this;
    }

    /**
     * @param string $id
     * @param string $label
     * @return $this
     */
    public function addRepeater($id, $label)
    {
        $this->add(new Repeater($id, $label));

        return $this;
    }

    /**
     * @param AbstractField $field
     * @return $this
     */
    public function addField(AbstractField $field)
    {
        $this->fieldKeys[] = $field->getId();
        $this->add($field);

        return $this;
    }

    /**
     * @param AbstractFieldsCollection $fieldsCollection
     * @return $this
     */
    public function addFields(AbstractFieldsCollection $fieldsCollection)
    {
        $fieldsCollection->each(function ($field) {
            $this->addField($field);
        });

        return $this;
    }

    /** @return $this */
    public function closeField()
    {
        $parentField = $this->getActiveItem()->getParent();

        if ($parentField) {
            $this->setActiveItem($parentField);
        } else {
            trigger_error('Failed to get parent field of Form. ' . $this->fieldPrefix, E_USER_WARNING);
        }

        return $this;
    }

    /** @return string */
    public function getType()
    {
        return 'form';
    }

    /** @return string */
    public function getFieldPrefix()
    {
        return $this->fieldPrefix;
    }

    /** @param string $fieldPrefix */
    public function setFieldPrefix($fieldPrefix): void
    {
        $this->fieldPrefix = $fieldPrefix;
    }

    /** @inheritDoc */
    public function toArray()
    {
        $items = $this->map(fn ($item) => $item->toArray());
        return $items->toArray();
    }

    /** @return string[] */
    public function getFieldKeys()
    {
        return $this->fieldKeys;
    }

    /**
     * @param AbstractComponent|class-string<AbstractComponent> $component
     * @param string $fieldPrefix
     * @return void
     */
    public function addComponentForm($component, $fieldPrefix)
    {
        $activeItem = $this->getActiveItem();

        $componentForm = $component::getForm();
        if (!is_object($componentForm)) {
            return;
        }

        $form = clone $componentForm;
        $form->setFieldPrefix($fieldPrefix);

        $this->addSection($fieldPrefix, $component::getName())->add($form);

        $this->setActiveItem($activeItem);
    }

    /**
     * @param Form|AbstractFieldsContainer $item
     * @return $this
     */
    public function setParent($item)
    {
        $this->parent = $item;
        return $this;
    }

    /** @return Form|AbstractFieldsContainer|null */
    public function getParent()
    {
        return $this->parent;
    }

    /** @return AbstractField[]|AbstractFieldsContainer[] */
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

    /** @return static */
    public static function create()
    {
        return new static();
    }
}
