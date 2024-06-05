<?php

namespace OffbeatWP\Content\Enqueue;

final class WpStyleEnqueueBuilder extends AbstractEnqueueBuilder
{
    protected string $media = 'all';

    /**
     * The media for which this stylesheet has been defined. Default 'all'.<br>
     * Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'
     * @return $this
     */
    public function setMedia(string $media)
    {
        $this->media = $media;
        return $this;
    }

    public function enqueue(string $handle): void
    {
        if ($this->src) {
            wp_enqueue_style($handle, $this->src, $this->deps, $this->version, $this->media);
        } else {
            wp_enqueue_style($handle);
        }
    }

    /** Returns a WpStyle instance if script was registered successfully or null if it was not. */
    public function register(string $handle): ?WpStyleHolder
    {
        if (wp_register_style($handle, $this->src, $this->deps, $this->version, $this->media)) {
            return new WpStyleHolder($handle);
        }

        return null;
    }
}