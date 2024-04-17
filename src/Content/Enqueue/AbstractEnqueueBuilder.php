<?php

namespace OffbeatWP\Content\Enqueue;

use OffbeatWP\Assets\AssetsManager;

abstract class AbstractEnqueueBuilder
{
    /** @var string[] */
    protected array $deps = [];
    protected string $src = '';
    protected ?string $version = null;

    /**
     * @param string $src The file location. Starts in theme stylesheet directory.
     * @return $this
     */
    final public function setSrc(string $src): self
    {
        $this->src = get_stylesheet_directory_uri() . '/' . $src;
        return $this;
    }

    /** @return $this */
    final public function setAsset(string $filename)
    {
        $this->src = offbeat(AssetsManager::class)->getUrl($filename) ?: '';

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
    final public function setDeps(...$deps)
    {
        if (count($deps) === 1 && is_array($deps[0])) {
            $this->deps = $deps[0];
        } else {
            $this->deps = $deps;
        }

        return $this;
    }

    /**
     * @param string $version String specifying stylesheet version number, if it has one, which is added to the URL as a query string for cache busting purposes
     * @return $this
     */
    final public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    abstract public function register(string $handle): ?AbstractAssetHolder;

    abstract public function enqueue(string $handle): void;
}