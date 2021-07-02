<?php
namespace OffbeatWP\Content\Post;

use Illuminate\Support\Collection;
use WP_Post;

class PostsCollection extends Collection
{
    protected $query = null;

    public function __construct($items)
    {
        if (is_object($items)) {
            $this->query = $items;

            $postItems = [];

            foreach ($items->posts as $post) {
                array_push($postItems, offbeat('post')->convertWpPostToModel($post));
            }

            $items = $postItems;
            $postItems = null;
        } elseif (is_array($items)) {
            foreach ($items as $itemKey => $item) {
                if ($item instanceof WP_Post) {
                    $items[$itemKey] = offbeat('post')->convertWpPostToModel($item);
                }
            }
        }

        parent::__construct($items);
    }

    public function getIterator(): WpPostsIterator
    {
        return new WpPostsIterator($this->items);
    }

    public function getQuery() {
        return $this->query;
    }

    /** @return PostModel|null */
    public function first(callable $callback = null, $default = null)
    {
        return parent::first($callback, $default);
    }

    /** @return PostModel|null */
    public function last(callable $callback = null, $default = null)
    {
        return parent::last($callback, $default);
    }

    /** @return PostModel|null */
    public function pop()
    {
        return parent::pop();
    }

    /** @return PostModel|null */
    public function pull($key, $default = null)
    {
        return parent::pull($key, $default);
    }

    /** @return PostModel|null */
    public function reduce(callable $callback, $initial = null)
    {
        return parent::reduce($callback, $initial);
    }

    /** @return PostModel|null */
    public function shift()
    {
        return parent::shift();
    }
}
