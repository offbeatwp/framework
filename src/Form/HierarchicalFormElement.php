<?php

namespace OffbeatWP\Form;

use Illuminate\Support\Collection;

/** For elements that extend this class can (optionally) have a parent and be used as a parent. */
abstract class HierarchicalFormElement extends Collection
{
    protected ?HierarchicalFormElement $parent = null;

    public function setParent(?HierarchicalFormElement $item): self
    {
        $this->parent = $item;
        return $this;
    }

    public function getParent(): ?HierarchicalFormElement
    {
        return $this->parent;
    }

    public function getLevel(): int
    {
        return 0;
    }
}