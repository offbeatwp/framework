<?php
namespace OffbeatWP\Form\FieldsCollections;

use ArrayIterator;
use IteratorAggregate;
use OffbeatWP\Form\Fields\FieldInterface;

abstract class AbstractFieldsCollection implements IteratorAggregate
{
    /** @var array<int, FieldInterface> */
    protected array $fields = [];

    public function addField(FieldInterface $field): void
    {
        $this->fields[] = $field;
    }

    public function addFields(AbstractFieldsCollection $fieldsCollection): void
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