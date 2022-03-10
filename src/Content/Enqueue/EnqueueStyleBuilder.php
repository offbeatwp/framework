<?php

namespace OffbeatWP\Content\Enqueue;

class EnqueueStyleBuilder extends AbstractEnqueueBuilder
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
        wp_enqueue_style($this->getHandle(), $this->src, $this->deps, $this->version, $this->media);
    }
}