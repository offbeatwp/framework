<?php
namespace OffbeatWP\Form;

use OffbeatWP\Components\AbstractComponent;
use OffbeatWP\Form\Fields\AbstractField;
use OffbeatWP\Form\FieldsCollections\FieldsCollectionInterface;
use OffbeatWP\Form\FieldsContainers\AbstractFieldsContainer;
use OffbeatWP\Form\FieldsContainers\FieldsContainerInterface;
use OffbeatWP\Form\FieldsContainers\Repeater;
use OffbeatWP\Form\FieldsContainers\Section;
use OffbeatWP\Form\FieldsContainers\Tab;
use OffbeatWP\Form\Fields\FieldInterface;

class Form extends FormElementCollection
{
    private FormElementCollection $activeItem;
    private array $fieldKeys = [];
    private string $fieldPrefix = '';

    public function __construct()
    {
        parent::__construct();
        $this->activeItem = $this;
    }

    /**
     * @param FieldInterface|FieldsContainerInterface|FieldsCollectionInterface|Form $item
     * @param bool $prepend
     * @return Form
     */
    public function add($item, bool $prepend = false): self
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

            if ($item instanceof AbstractFieldsContainer) {
                $this->setActiveItem($item);
                $this->setParent($item);
            }

            return $this;
        }

        // Add item to current active item
        $this->getActiveItem()->add($item);

        // If item is instance of Fields Container, change the active item.
        if ($item instanceof AbstractFieldsContainer) {
            $this->setActiveItem($item);
            $this->setParent($item);
        }

        return $this;
    }

    public function getActiveItem(): FormElementCollection
    {
        return $this->activeItem;
    }

    public function setActiveItem(FormElementCollection $item): self
    {
        $this->activeItem = $item;
        return $this;
    }

    public function addTab(string $id, string $label)
    {
        $this->add(new Tab($id, $label));

        return $this;
    }

    public function addSection(string $id, string $label): self
    {
        $this->add(new Section($id, $label));
        return $this;
    }

    public function addRepeater(string $id, string $label): self
    {
        $this->add(new Repeater($id, $label));
        return $this;
    }

    public function addField(FieldInterface $field): self
    {
        $this->fieldKeys[] = $field->getId();
        $this->add($field);

        return $this;
    }

    public function addFields(FormElementCollection $fieldsCollection): self
    {
        $fieldsCollection->each(function ($field) {
            $this->addField($field);
        });

        return $this;
    }

    public function closeField(): self
    {
        $parentField = $this->getActiveItem()->getParent();

        if ($parentField) {
            $this->setActiveItem($parentField);
        }

        return $this;
    }

    public function getType(): string
    {
        return 'form';
    }

    public function getFieldPrefix(): string
    {
        return $this->fieldPrefix;
    }

    public function setFieldPrefix(string $fieldPrefix): self
    {
        $this->fieldPrefix = $fieldPrefix;
        return $this;
    }

    public function toArray(): array
    {
        $items = $this->map(function ($item) {
            return $item->toArray();
        });

        return $items->toArray();
    }

    /** @return string[] */
    public function getFieldKeys(): array
    {
        return $this->fieldKeys;
    }

    public function addComponentForm(AbstractComponent $component, string $fieldPrefix): self
    {
        $activeItem = $this->getActiveItem();

        $form = clone $component::getForm();
        $form->setFieldPrefix($fieldPrefix);

        $this->addSection($fieldPrefix, $component::getName())->add($form);
        $this->setActiveItem($activeItem);

        return $this;
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

    public static function create(): self
    {
        return new self();
    }
}
