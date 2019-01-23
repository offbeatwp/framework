<?php
namespace OffbeatWP\Form\FieldsCollections;

use OffbeatWp\Form\Fields\FieldInterface;
use Illuminate\Support\Collection;

class AbstractFieldsCollection extends Collection implements FieldsCollectionInterface {
    public function addField(FieldInterface $field) {
        $this->push($field);
    }

    public function addFieldsCollection(FieldsCollectionInterface $fieldsCollection) {
        $fieldsCollection->each(function ($field) {
            $this->addField($field);
        });
    }    
}