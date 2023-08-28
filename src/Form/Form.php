<?php
namespace OffbeatWP\Form;

use Illuminate\Support\Collection;
use OffbeatWP\Components\AbstractComponent;
use OffbeatWP\Form\Fields\AbstractField;
use OffbeatWP\Form\FieldsCollections\FieldsCollectionInterface;
use OffbeatWP\Form\FieldsContainers\AbstractFieldsContainer;
use OffbeatWP\Form\FieldsContainers\FieldsContainerInterface;
use OffbeatWP\Form\FieldsContainers\Repeater;
use OffbeatWP\Form\FieldsContainers\Section;
use OffbeatWP\Form\FieldsContainers\Tab;
use OffbeatWP\Form\Fields\FieldInterface;

class Form extends Collection
{
    /** @var Form|FieldsContainerInterface */
    private $activeItem;
    /** @var string[] */
    private $fieldKeys = [];
    /** @var string */
    private $fieldPrefix = '';
    /** @var Form|FieldsContainerInterface */
    public $parent;

    public function __construct()
    {
        parent::__construct();
        $this->activeItem = $this;
    }

    /**
     * @param FieldInterface|FieldsContainerInterface|FieldsCollectionInterface|Form $item
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

    /** @return Form|FieldsContainerInterface */
    public function getActiveItem()
    {
        return $this->activeItem;
    }

    /**
     * @param Form|FieldsContainerInterface $item
     * @param bool $setParent
     * @return Form|FieldsContainerInterface
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
     * @param FieldInterface $field
     * @return $this
     */
    public function addField(FieldInterface $field)
    {
        $this->fieldKeys[] = $field->getId();
        $this->add($field);

        return $this;
    }

    /**
     * @param FieldsCollectionInterface $fieldsCollection
     * @return $this
     */
    public function addFields(FieldsCollectionInterface $fieldsCollection)
    {
        $fieldsCollection->each(function ($field) {
            $this->addField($field);
        });

        return $this;
    }

    /**
     * @return $this
     */
    public function closeField()
    {
        $parentField = $this->getActiveItem()->getParent();

        if ($parentField) {
            $this->setActiveItem($parentField);
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
    public function setFieldPrefix($fieldPrefix) 
    {
        $this->fieldPrefix = $fieldPrefix;
    }

    /** @inheritDoc */
    public function toArray()
    {
        $items = $this->map(fn($item) => $item->toArray());
        return $items->toArray();
    }

    /** @return string[] */
    public function getFieldKeys()
    {
        return $this->fieldKeys;
    }

    /**
     * @param AbstractComponent $component
     * @param string $fieldPrefix
     * @return void
     */
    public function addComponentForm($component, $fieldPrefix) {
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
     * @param Form|FieldsContainerInterface $item
     * @return void
     */
    public function setParent($item)
    {
        $this->parent = $item;
    }

    /** @return Form|FieldsContainerInterface */
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
