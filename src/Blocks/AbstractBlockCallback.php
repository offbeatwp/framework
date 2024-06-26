<?php

namespace OffbeatWP\Blocks;

use OffbeatWP\Views\ViewableTrait;
use WP_Block;

abstract class AbstractBlockCallback
{
    use ViewableTrait;

    /** @var mixed[] */
    protected array $attributes;
    protected string $content;
    protected WP_Block $wpBlock;

    /** @param mixed[] $attributes */
    final public function __construct(array $attributes, string $content, WP_Block $wpBlock)
    {
        $this->attributes = $attributes;
        $this->content = $content;
        $this->wpBlock = $wpBlock;
    }

    /** @return mixed[] */
    final public function getAttributes(): array
    {
        return $this->attributes;
    }

    /** @return mixed */
    final public function getAttribute(string $index)
    {
        return $this->attributes[$index] ?? null;
    }

    final public function getContent(): string
    {
        return $this->content;
    }

    /** @return \WP_Block_List|array{} */
    final public function getInnerBlocks()
    {
        return $this->wpBlock->inner_blocks;
    }

    /** @return mixed[] */
    final public function getContext(): array
    {
        return $this->wpBlock->context;
    }

    /** @param mixed[] $attributes */
    final public static function renderBlock(array $attributes, string $content, WP_Block $wpBlock): string
    {
        return (new static($attributes, $content, $wpBlock))->render();
    }

    abstract public function render(): string;
}
