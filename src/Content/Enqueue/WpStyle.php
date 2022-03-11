<?php

namespace OffbeatWP\Content\Enqueue;

class WpStyle extends AbstractEnqueueBuilder
{
    protected $media = 'all';

    /**
     * The media for which this stylesheet has been defined. Default 'all'. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'
     * @return static
     */
    public function setMedia(string $media)
    {
        $this->media = $media;
        return $this;
    }

    public function enqueue(): void
    {
        if ($this->registered) {
            wp_enqueue_style($this->getHandle());
        } else {
            wp_enqueue_style($this->getHandle(), $this->src, $this->deps, $this->version, $this->media);
        }
    }

    /** @return static */
    public function register()
    {
        wp_register_style($this->getHandle(), $this->src, $this->deps, $this->version, $this->media);
        $this->registered = true;
        return $this;
    }
}