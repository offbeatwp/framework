<?php
namespace OffbeatWP\Form\FieldsCollections;

use ArrayIterator;
use IteratorAggregate;
use OffbeatWP\Form\Fields\FieldInterface;

abstract class AbstractFieldsCollection implements FieldsCollectionInterface, IteratorAggregate
{
    /** @var array<int, FieldInterface> */
    protected array $fields = [];

    /**
     * @param FieldInterface $field
     * @return void
     */
    public function addField(FieldInterface $field): void
    {
        $this->fields[] = $field;
    }

    /**
     * @param FieldsCollectionInterface $fieldsCollection
     * @return void
     */
    public function addFields(FieldsCollectionInterface $fieldsCollection): void
    {
        foreach ($fieldsCollection as $field) {
            $this->fields[] = $field;
        }
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->fields);
    }
}