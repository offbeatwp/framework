<?php

namespace OffbeatWP\Content\Post;

use Iterator;
use WP_Post;

class WpPostsIterator implements Iterator
{
    /** @var PostModel[] */
    protected array $items;
    /** @var WP_Post|null */
    private $originalPost = null;
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
            $this->originalPost = $GLOBALS['post'];
            $this->globalPostWasChanged = true;

            $item = current($this->items)->wpPost;
            $GLOBALS['post'] = $item;
            setup_postdata($item);

            return true;
        }

        if ($this->globalPostWasChanged) {
            $GLOBALS['post'] = $this->originalPost;
            $this->originalPost = null;
            $this->globalPostWasChanged = false;
        }

        wp_reset_query();
        wp_reset_postdata();

        return false;
    }
}