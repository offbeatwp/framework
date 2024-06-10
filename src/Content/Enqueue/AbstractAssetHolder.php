<?php

namespace OffbeatWP\Content\Enqueue;

abstract class AbstractAssetHolder
{
    protected string $handle;

    final public function __construct(string $handle)
    {
        $this->handle = $handle;
    }

    abstract public function enqueue(): void;
}
