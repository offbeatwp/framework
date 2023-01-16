<?php
namespace OffbeatWP\Form\FieldsCollections;

use OffbeatWP\Form\Fields\FieldInterface;
use Illuminate\Support\Collection;

class AbstractFieldsCollection extends Collection implements FieldsCollectionInterface {
    public function addField(FieldInterface $field) {
        $this->push($field);
    }

    public function addFields(FieldsCollectionInterface $fieldsCollection) {
        $fieldsCollection->each(function ($field) {
            $this->addField($field);
        });
    }    
}