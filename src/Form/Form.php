<?php
namespace OffbeatWP\Form;

use OffbeatWP\Components\AbstractComponent;
use OffbeatWP\Form\Fields\AbstractField;
use OffbeatWP\Form\FieldsCollections\AbstractFieldsCollection;
use OffbeatWP\Form\FieldsContainers\AbstractFormContainer;
use OffbeatWP\Form\FieldsContainers\Repeater;
use OffbeatWP\Form\FieldsContainers\Section;
use OffbeatWP\Form\FieldsContainers\Tab;

final class Form extends FormParentItem
{
    private FormParentItem $activeItem;
    private array $fieldKeys = [];
    private string $fieldPrefix = '';

    public function __construct()
    {
        parent::__construct();
        $this->activeItem = $this;
    }

    /**
     * @param AbstractField|AbstractFormContainer|AbstractFieldsCollection|Form $item
     * @param bool $prepend
     * @return Form
     */
    public function add($item, bool $prepend = false): self
    {
        // If item is Tab and active item is Section move back to parent
        if ($this->getActiveItem() !== $this && $item instanceof AbstractFormContainer) {
            while ($item->getLevel() < $this->getActiveItem()->getLevel()) {
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

            if ($item instanceof AbstractFormContainer) {
                $this->setActiveItem($item);
                $this->setParent($item);
            }

            return $this;
        }

        // Add item to current active item
        $this->getActiveItem()->add($item);

        // If item is instance of Fields Container, change the active item.
        if ($item instanceof FormParentItem) {
            $this->setActiveItem($item);
            $this->setParent($item);
        }

        return $this;
    }

    public function getActiveItem(): FormParentItem
    {
        return $this->activeItem;
    }

    public function setActiveItem(FormParentItem $item): self
    {
        $this->activeItem = $item;
        return $this;
    }

    public function addTab(string $id, string $label): self
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

    public function addField(AbstractField $field): self
    {
        $this->fieldKeys[] = $field->getId();
        $this->push($field);

        return $this;
    }

    public function addFields(iterable $fields): self
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }

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
            if ($item instanceof AbstractField || $item instanceof AbstractFormContainer) {
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
