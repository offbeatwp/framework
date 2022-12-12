<?php

namespace OffbeatWP\Content\Post;

use Iterator;

class WpPostsIterator implements Iterator
{
    /** @var PostModel[] */
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

    public function current()
    {
        return current($this->items);
    }

    public function key()
    {
        return key($this->items);
    }

    public function next(): void
    {
        next($this->items);
    }

    public function valid(): bool
    {
        if (key($this->items) !== null) {
            $item = current($this->items)->wpPost;

            if (!$this->globalPostWasChanged) {
                $this->globalPostWasChanged = true;
                $this->originalPost = $GLOBALS['post'] ?? null;
            }

            $GLOBALS['post'] = $item;
            setup_postdata($item);

            return true;
        }

        if ($this->globalPostWasChanged) {
            $GLOBALS['post'] = $this->originalPost;
            $this->globalPostWasChanged = false;
        }

        wp_reset_query();
        return false;
    }
}