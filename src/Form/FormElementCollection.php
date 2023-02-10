<?php

namespace OffbeatWP\Form;

use Illuminate\Support\Collection;

abstract class FormElementCollection extends Collection
{
    protected ?FormElementCollection $parent = null;

    public function setParent(?FormElementCollection $item): self
    {
        $this->parent = $item;
        return $this;
    }

    public function getParent(): ?FormElementCollection
    {
        return $this->parent;
    }

    public function getLevel(): int
    {
        return 0;
    }
}