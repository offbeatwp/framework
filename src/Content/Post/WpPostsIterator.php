<?php

namespace OffbeatWP\Content\Post;

use Iterator;

class WpPostsIterator implements Iterator
{
    /** @var PostModel[] */
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

    /** @return array-key|null */
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
            $originalPost = $GLOBALS['post'];

            $item = current($this->items)->wpPost;
            $GLOBALS['post'] = $item;
            setup_postdata($item);

            $GLOBALS['post'] = $originalPost;

            return true;
        }

        wp_reset_query();
        wp_reset_postdata();

        return false;
    }
}