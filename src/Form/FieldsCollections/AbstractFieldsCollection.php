<?php
namespace OffbeatWP\Form\FieldsCollections;

use Illuminate\Support\Collection;
use OffbeatWP\Form\Fields\AbstractField;

abstract class AbstractFieldsCollection extends Collection {
    public function addField(AbstractField $field): self
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