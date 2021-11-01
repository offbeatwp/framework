<?php
namespace OffbeatWP\Form\FieldsCollections;

use Illuminate\Support\Collection;
use OffbeatWP\Form\Fields\FieldInterface;

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