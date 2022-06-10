<?php

namespace OffbeatWP\Content\Enqueue;

final class WpScriptHolder extends AbstractAssetHolder
{
    public function enqueue(): void
    {
        wp_enqueue_script($this->handle);
    }
}