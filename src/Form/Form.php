<?php
namespace OffbeatWP\Form;

use Illuminate\Support\Collection;
use OffbeatWP\Components\AbstractComponent;
use OffbeatWP\Form\Fields\AbstractField;
use OffbeatWP\Form\FieldsCollections\FieldsCollectionInterface;
use OffbeatWP\Form\FieldsContainers\FieldsContainerInterface;
use OffbeatWP\Form\FieldsContainers\Repeater;
use OffbeatWP\Form\FieldsContainers\Section;
use OffbeatWP\Form\FieldsContainers\Tab;

class Form extends Collection
{
    /** @var Form|FieldsContainerInterface */
    private $activeItem;
    private $fieldKeys = [];
    private $fieldPrefix = '';
    /** @var Form|FieldsContainerInterface|null */
    public $parent;

    public function __construct()
    {
        parent::__construct();
        $this->activeItem = $this;
    }

    public function add($item, bool $prepend = false): Form
    {
        // If item is Tab and active item is Section move back to parent
        if (($this->getActiveItem() !== $this) && $item instanceof FieldsContainerInterface) {
            while ($item::LEVEL < $this->getActiveItem()::LEVEL) {
                $this->closeField();
            }
        }

        // If item is of the same type as the active item move back to parent
        $itemClass = get_class($item);
        if ($itemClass === get_class($this->getActiveItem()) && $itemClass !== self::class) {
            $this->closeField();
        }

        // If active item is the form, push item directly the items
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

    public function setActiveItem($item, bool $setParent = false)
    {
        if ($setParent) {
            $item->setParent($this->getActiveItem());
        }

        $this->activeItem = $item;

        return $this->activeItem;
    }

    public function addTab(string $id, string $label): Form
    {
        $this->add(new Tab($id, $label));

        return $this;
    }

    public function addSection(string $id, string $label): Form
    {
        $this->add(new Section($id, $label));

        return $this;
    }

    public function addRepeater(string $id, string $label): Form
    {
        $this->add(new Repeater($id, $label));

        return $this;
    }

    public function addField(AbstractField $field): Form
    {
        $this->fieldKeys[] = $field->getId();
        $this->add($field);

        return $this;
    }

    public function addFields(FieldsCollectionInterface $fieldsCollection): Form
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

    public function getFieldPrefix(): string
    {
        return $this->fieldPrefix;
    }

    public function setFieldPrefix(string $fieldPrefix): void
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

    /** @param class-string<AbstractComponent>|AbstractComponent $component */
    public function addComponentForm($component, string $fieldPrefix): void
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

    /** @param Form|FieldsContainerInterface $item */
    public function setParent($item): void
    {
        $this->parent = $item;
    }

    public function getParent()
    {
        return $this->parent;
    }
}
