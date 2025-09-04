<?php

namespace OffbeatWP\Blocks;

use WP_Block;

/** @internal */
interface IBlockCallback
{
    /** @param array<string, mixed> $attributes */
    public function __construct(array $attributes, string $content, WP_Block $wpBlock);
}
