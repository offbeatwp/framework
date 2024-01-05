<?php

namespace OffbeatWP\Content\Post;

use ArrayIterator;

/** @template TModel of PostModel */
class WpPostsIterator extends ArrayIterator
{
    /** @var \WP_Post|null */
    private $originalPost;
    private bool $globalPostWasChanged = false;

    /**
     * @return PostModel|false
     * @phpstan-return TModel|false
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return parent::current();
    }

    /**
     * Many WordPress methods rely on the global post object.<br>
     * In the valid method we setup the global post object to ensure that these methods work properly during this iteration of the loop.
     */
    public function valid(): bool
    {
        if (key($this) !== null) {
            $item = current($this)->wpPost;

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
