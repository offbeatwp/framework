<?php

namespace OffbeatWP\Content\Enqueue;

abstract class AbstractEnqueueBuilder
{
    /** @var string */
    private $handle;
    /** @var string */
    protected $src = '';
    /** @var string[] */
    protected $deps = [];
    /** @var null|false|string */
    protected $version = null;

    public function __construct(string $handle)
    {
        $this->handle = $handle;
    }

    /** @param string $src The file location. */
    public function setSrc(string $src)
    {
        $this->src = get_stylesheet_directory_uri() . $src;
    }

    /**
     * @param string[] $deps An array of registered handles that this enqueue depends on.
     * @return static
     */
    public function setDeps(string ...$deps)
    {
        $this->deps = $deps;
        return $this;
    }

    /**
     * @param string $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes
     * @return static
     */
    public function setVersion(string $version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Add version number for cache busting equal to current installed WordPress version
     * @return static
     */
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