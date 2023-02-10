<?php

namespace OffbeatWP\Form;

use OffbeatWP\Form\FieldsCollections\FieldsCollection;

abstract class FieldsContainer extends FieldsCollection
{
    protected ?FieldsContainer $parent = null;

    public function setParent(?FieldsContainer $item): self
    {
        $this->parent = $item;
        return $this;
    }

    public function getParent(): ?FieldsContainer
    {
        return $this->parent;
    }

    public function getLevel(): int
    {
        return 0;
    }
}