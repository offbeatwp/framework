<?php

namespace OffbeatWP\Content\Enqueue;

abstract class AbstractEnqueueBuilder
{
    protected string $src = '';
    /** @var string[] */
    protected array $deps = [];
    /** @var null|false|string */
    protected $version = null;

    /**
     * @param string $src The file location. Starts in theme stylesheet directory.
     * @return $this
     */
    final public function setSrc(string $src)
    {
        $this->src = get_stylesheet_directory_uri() . '/' . $src;
        return $this;
    }

    /** @return $this */
    final public function setAsset(string $filename)
    {
        $this->src = offbeat('assets')->getUrl($filename) ?: '';

        if (!$this->src) {
            trigger_error('Could not find asset url for ' . $filename, E_USER_WARNING);
        }

        return $this;
    }

    /**
     * @param string $src The file location.
     * @return $this
     */
    final public function setAbsoluteSrc(string $src)
    {
        $this->src = $src;
        return $this;
    }

    /**
     * @param string[] $deps An array of registered handles that this enqueue depends on.
     * @return $this
     */
    final public function setDeps(array $deps)
    {
        $this->deps = $deps;
        return $this;
    }

    /**
     * @param string $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes
     * @return $this
     */
    final public function setVersion(string $version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Add version number for cache busting equal to current installed WordPress version<br>
     * Beware that this might make it easier for attackers to find your currently installed WordPress version.
     * @return $this
     */
    final public function setVersionToWpVersion()
    {
        $this->version = false;
        return $this;
    }

    abstract public function register(string $handle): ?AbstractAssetHolder;

    abstract public function enqueue(string $handle): void;
}