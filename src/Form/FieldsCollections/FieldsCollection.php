<?php
namespace OffbeatWP\Form\FieldsCollections;

use OffbeatWP\Form\Fields\FieldInterface;
use Illuminate\Support\Collection;

abstract class FieldsCollection extends Collection implements FieldsCollectionInterface {
    public function addField(FieldInterface $field): self
    {
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
}