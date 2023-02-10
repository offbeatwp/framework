<?php

namespace OffbeatWP\Form;

use Illuminate\Support\Collection;

abstract class FormElementCollection extends Collection
{
    protected $parent = null;

    public function setParent($item): self
    {
        $this->parent = $item;
        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }
}