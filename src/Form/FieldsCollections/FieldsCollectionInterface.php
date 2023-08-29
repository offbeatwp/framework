<?php
namespace OffbeatWP\Form\FieldsCollections;

interface FieldsCollectionInterface {
    /** @return self */
    public function each(callable $callback);
}