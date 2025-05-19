<?php

namespace OffbeatWP\Blocks;

use OffbeatWP\Views\ViewableTrait;
use WP_Block;

abstract class AbstractBlockCallback implements IBlockCallback
{
    use ViewableTrait;

    /** @var array<string, mixed> */
    protected array $attributes;
    protected string $content;
    protected WP_Block $wpBlock;

    /** @param array<string, mixed> $attributes */
    public function __construct(array $attributes, string $content, WP_Block $wpBlock)
    {
        $this->attributes = $attributes;
        $this->content = $content;
        $this->wpBlock = $wpBlock;
    }

    /** @return array<string, mixed> */
    final public function getAttributes(): array
    {
        return $this->attributes;
    }

    final public function getAttribute(string $index): mixed
    {
        return $this->attributes[$index] ?? null;
    }

    final public function getContent(): string
    {
        return $this->content;
    }

    /** @return \WP_Block_List|WP_Block[] */
    final public function getInnerBlocks()
    {
        return $this->wpBlock->inner_blocks;
    }

    /** @return array<string, mixed> */
    final public function getContext(): array
    {
        return $this->wpBlock->context;
    }

    /** @param array<string, mixed> $attributes */
    final public static function renderBlock(array $attributes, string $content, WP_Block $wpBlock): string
    {
        return (new static($attributes, $content, $wpBlock))->render();
    }

    abstract public function render(): string;
}
