<?php
namespace OffbeatWP\Form\FieldsCollections;

use OffbeatWP\Form\Fields\AbstractField;
use Illuminate\Support\Collection;

class AbstractFieldsCollection extends Collection implements FieldsCollectionInterface {
    public function addField(AbstractField $field) {
        $this->push($field);
    }

    public function addFields(FieldsCollectionInterface $fieldsCollection) {
        $fieldsCollection->each(function ($field) {
            $this->addField($field);
        });
    }    
}