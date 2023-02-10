<?php

namespace OffbeatWP\Form;

use Illuminate\Support\Collection;

/** For elements that extend this class can (optionally) have a parent and be used as a parent. */
abstract class FormParentItem extends Collection
{
    protected ?FormParentItem $parent = null;

    public function setParent(?FormParentItem $item): self
    {
        $this->parent = $item;
        return $this;
    }

    public function getParent(): ?FormParentItem
    {
        return $this->parent;
    }

    public function getLevel(): int
    {
        return 0;
    }
}