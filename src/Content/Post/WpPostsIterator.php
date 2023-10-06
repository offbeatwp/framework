<?php

namespace OffbeatWP\Content\Post;

use Iterator;

/** @template TModel of PostModel */
class WpPostsIterator implements Iterator
{
    /**
     * @var PostModel[]
     * @phpstan-var TModel[]
     */
    protected array $items;
    private $originalPost;
    private bool $globalPostWasChanged = false;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function rewind(): void
    {
        reset($this->items);
    }

    /**
     * @return PostModel|false
     * @phpstan-return TModel|false
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->items);
    }

    /** @return int|string|null */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->items);
    }

    public function next(): void
    {
        next($this->items);
    }

    /**
     * Many WordPress methods rely on the global post object.<br>
     * In the valid method we setup the global post object to ensure that these methods work properly during this iteration of the loop.
     * @return bool
     */
    public function valid(): bool
    {
        if (key($this->items) !== null) {
            $item = current($this->items)->wpPost;

            // Remember the old value of the post global so that we can put it back after the loop is finished.
            if (!$this->globalPostWasChanged) {
                $this->globalPostWasChanged = true;
                $this->originalPost = $GLOBALS['post'] ?? null;
            }

            $GLOBALS['post'] = $item;
            setup_postdata($item);

            return true;
        }

        // The loop is finished; put back the global post object to the value it had before the loop was started.
        if ($this->globalPostWasChanged) {
            $GLOBALS['post'] = $this->originalPost;
            $this->globalPostWasChanged = false;
        }

        wp_reset_query();
        return false;
    }
}
