<?php
namespace OffbeatWP\Form;

use ArrayIterator;
use IteratorAggregate;
use OffbeatWP\Components\AbstractComponent;
use OffbeatWP\Form\Fields\AbstractField;
use OffbeatWP\Form\FieldsCollections\AbstractFieldsCollection;
use OffbeatWP\Form\FieldsContainers\AbstractFieldsContainer;
use OffbeatWP\Form\FieldsContainers\Repeater;
use OffbeatWP\Form\FieldsContainers\Section;
use OffbeatWP\Form\FieldsContainers\Tab;
use Traversable;

/** @implements IteratorAggregate<int, AbstractField|AbstractFieldsContainer|AbstractFieldsCollection|Form> */
final class Form implements IteratorAggregate
{
    public const LEVEL = 0;

    /** @var array<int, string> */
    private array $fieldKeys = [];
    /** @var array<int, AbstractField|AbstractFieldsContainer|AbstractFieldsCollection|Form> */
    private array $items = [];
    private string $fieldPrefix = '';
    private AbstractFieldsContainer|Form|null $parent = null;
    private AbstractFieldsContainer|Form $activeItem;

    public function __construct()
    {
        $this->activeItem = $this;
    }

    public function add(AbstractField|AbstractFieldsContainer|AbstractFieldsCollection|Form $item, bool $prepend = false): Form
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
                array_unshift($this->items, $item);
            } else {
                $this->items[] = $item;
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

    public function getActiveItem(): Form|AbstractFieldsContainer
    {
        return $this->activeItem;
    }

    public function setActiveItem(Form|AbstractFieldsContainer $item, bool $setParent = false): Form|AbstractFieldsContainer
    {
        if ($setParent) {
            $item->setParent($this->getActiveItem());
        }

        $this->activeItem = $item;

        return $this->activeItem;
    }

    /** @return $this */
    public function addTab(string $id, string $label)
    {
        $this->add(new Tab($id, $label));
        return $this;
    }

    /** @return $this */
    public function addSection(string $id, string $label)
    {
        $this->add(new Section($id, $label));
        return $this;
    }

    /** @return $this */
    public function addRepeater(string $id, string $label)
    {
        $this->add(new Repeater($id, $label));

        return $this;
    }

    /** @return $this */
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
        foreach ($fieldsCollection as $field) {
            $this->addField($field);
        }

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

    /** @return $this */
    public function setFieldPrefix(string $fieldPrefix)
    {
        $this->fieldPrefix = $fieldPrefix;
        return $this;
    }

    public function toArray(): array
    {
        return array_map(fn($item) => $item->toArray(), $this->items);
    }

    /** @return string[] */
    public function getFieldKeys()
    {
        return $this->fieldKeys;
    }

    public function addComponentForm(AbstractComponent $component, string $fieldPrefix): void
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

    /** @return $this */
    public function setParent(Form|AbstractFieldsContainer $item)
    {
        $this->parent = $item;
        return $this;
    }

    public function getParent(): Form|AbstractFieldsContainer|null
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

    public static function create(): static
    {
        return new static();
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
