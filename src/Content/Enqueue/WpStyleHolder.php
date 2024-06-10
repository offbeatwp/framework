<?php

namespace OffbeatWP\Content\Enqueue;

final class WpStyleHolder extends AbstractAssetHolder
{
    public function enqueue(): void
    {
        wp_enqueue_style($this->handle);
    }
}
