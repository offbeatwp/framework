<?php
namespace OffbeatWP\Form\FieldsCollections;

use ArrayIterator;
use IteratorAggregate;
use OffbeatWP\Form\Fields\AbstractField;

abstract class AbstractFieldsCollection implements IteratorAggregate
{
    /** @var array<int, AbstractField> */
    protected array $fields = [];

    public function addField(AbstractField $field): void
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