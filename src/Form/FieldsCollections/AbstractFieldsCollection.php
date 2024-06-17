<?php

namespace OffbeatWP\Form\FieldsCollections;

use OffbeatWP\Form\Fields\FieldInterface;
use Illuminate\Support\Collection;

/** @extends Collection<int, \OffbeatWP\Form\Fields\AbstractField> */
class AbstractFieldsCollection extends Collection implements FieldsCollectionInterface
{
    /**
     * @param \OffbeatWP\Form\Fields\AbstractField $field
     * @return void
     */
    public function addField(FieldInterface $field)
    {
        $this->push($field);
    }

    /**
     * @param FieldsCollectionInterface $fieldsCollection
     * @return void
     */
    public function addFields(FieldsCollectionInterface $fieldsCollection)
    {
        $fieldsCollection->each(function ($field) {
            $this->addField($field);
        });
    }
}
