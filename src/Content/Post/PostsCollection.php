<?php
namespace OffbeatWP\Content\Post;

use Illuminate\Support\Collection;
use WP_Post;
use WP_Query;

/** @template T */
class PostsCollection extends Collection
{
    /** @var WP_Query|object|null */
    protected $query = null;

    public function __construct($items)
    {
        if (is_object($items)) {
            $this->query = $items;

            $postItems = [];

            foreach ($items->posts as $post) {
                $postItems[] = offbeat('post')->convertWpPostToModel($post);
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

    public function getQuery()
    {
        return $this->query;
    }

    /** Returns this PostsCollection as a generic Collection */
    public function toCollection(): Collection
    {
        return collect($this->toArray());
    }

    public function map(callable $callback): Collection
    {
        $keys = array_keys($this->items);

        $items = array_map($callback, $this->items, $keys);

        return new Collection(array_combine($keys, $items));
    }

    /** @return T|null */
    public function first(callable $callback = null, $default = null): ?PostModel
    {
        return parent::first($callback, $default);
    }

    /** @return T|null */
    public function last(callable $callback = null, $default = null): ?PostModel
    {
        return parent::last($callback, $default);
    }

    /** @return T|null */
    public function pop($count = 1): ?PostModel
    {
        return parent::pop($count);
    }

    /** @return T|null */
    public function pull($key, $default = null): ?PostModel
    {
        return parent::pull($key, $default);
    }

    /** @return T|null */
    public function reduce(callable $callback, $initial = null): ?PostModel
    {
        return parent::reduce($callback, $initial);
    }

    /** @return T|null */
    public function shift($count = 1): ?PostModel
    {
        return parent::shift($count);
    }
}
