<?php

namespace OffbeatWP\Content\Enqueue;

abstract class AbstractEnqueueBuilder
{
    private $handle;
    protected $src = '';
    protected $deps = [];
    protected $version = null;

    public function __construct(string $handle)
    {
        $this->handle = $handle;
    }

    /** An array of registered handles that this enqueue depends on */
    public function setDeps(string ...$deps)
    {
        $this->deps = $deps;
        return $this;
    }

    /** String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes */
    public function setVersion(string $version)
    {
        $this->version = $version;
        return $this;
    }

    /** Add version number for cache busting equal to current installed WordPress version */
    public function setVersionToWpVersion()
    {
        $this->version = false;
        return $this;
    }

    final protected function getHandle(): string
    {
        return $this->handle;
    }

    abstract public function enqueue(): void;
}