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

    final public function getAttributeString(string $index): ?string
    {
        return $this->filterAttribute($index, FILTER_DEFAULT);
    }

    final public function getAttributeInt(string $index): ?int
    {
        return $this->filterAttribute($index, FILTER_VALIDATE_INT);
    }

    final public function getAttributeFloat(string $index): ?float
    {
        return $this->filterAttribute($index, FILTER_VALIDATE_FLOAT);
    }

    final public function getAttributeBool(string $index): ?bool
    {
        return $this->filterAttribute($index, FILTER_VALIDATE_BOOL);
    }

    private function filterAttribute(string $index, int $filter): mixed
    {
        return filter_var($this->attributes[$index] ?? null, $filter, FILTER_NULL_ON_FAILURE);
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

    /** @param array<non-falsy-string, scalar|null> $extraAttributes */
    final protected function getBlockWrapperAttributes(array $extraAttributes = []): string
    {
        return str_replace('wp-block-', '', get_block_wrapper_attributes($extraAttributes));
    }

    abstract public function render(): string;
}
