<?php

namespace OffbeatWP\Content\Enqueue;

abstract class AbstractEnqueueBuilder
{
    protected string $src = '';
    /** @var string[] */
    protected array $deps = [];
    /** @var null|false|string */
    protected $version = null;

    /** @param string $src The file location. Starts in theme stylesheet directory. */
    final public function setSrc(string $src): self
    {
        $this->src = get_stylesheet_directory_uri() . '/' . $src;
        return $this;
    }

    final public function setAsset(string $filename): self
    {
        $this->src = offbeat('assets')->getUrl($filename) ?: '';

        if (!$this->src) {
            trigger_error('Could not find asset url for ' . $filename, E_USER_WARNING);
        }

        return $this;
    }

    /** @param string $src The file location. */
    final public function setAbsoluteSrc(string $src): self
    {
        $this->src = $src;
        return $this;
    }

    /** @param string[] $deps An array of registered handles that this enqueue depends on. */
    final public function setDeps(...$deps): self
    {
        if (count($deps) === 1 && is_array($deps[0])) {
            $this->deps = $deps[0];
        } else {
            $this->deps = $deps;
        }

        return $this;
    }

    /** @param string $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes */
    final public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @deprecated
     * Add version number for cache busting equal to current installed WordPress version<br>
     * This makes it easy for attackers to find the WordPress version and thus should not be used
     */
    final public function setVersionToWpVersion(): self
    {
        $this->version = false;
        return $this;
    }

    abstract public function register(string $handle): ?AbstractAssetHolder;

    abstract public function enqueue(string $handle): void;
}