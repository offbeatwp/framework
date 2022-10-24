<?php

namespace OffbeatWP\Content\Meta;

abstract class MetaBuilder
{
    protected string $metaKey;
    protected string $metaType;
    protected string $subType;
    /** @var callable */
    protected $resolver;

    public function __constructor(string $metaKey, string $metaType, string $subType)
    {
        $this->metaKey = $metaKey;
        $this->metaType = $metaType;
        $this->subType = $subType;
    }

    abstract public function register(): bool;

    abstract protected function getType(): string;

    public function setResolver(callable $resolver): void
    {
        $this->resolver = $resolver;
    }
}