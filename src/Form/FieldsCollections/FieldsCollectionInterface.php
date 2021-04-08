<?php
namespace OffbeatWP\Form\FieldsCollections;

interface FieldsCollectionInterface {
    public function each(callable $callback);
}