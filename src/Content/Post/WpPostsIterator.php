<?php

namespace OffbeatWP\Content\Post;

use Iterator;

class WpPostsIterator implements Iterator
{
    protected $items;

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

    public function next()
    {
        next($this->items);
    }

    public function valid(): bool
    {
        if (key($this->items) !== null) {
            $item = current($this->items);

            $GLOBALS['post'] = $item = $item->wpPost;
            setup_postdata($item);

            return true;
        }

        wp_reset_query();
        wp_reset_postdata();

        return false;
    }
}