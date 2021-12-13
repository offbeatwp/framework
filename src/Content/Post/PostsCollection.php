<?php
namespace OffbeatWP\Content\Post;

use Illuminate\Support\Collection;
use OffbeatWP\Exceptions\OffbeatCollectionException;
use WP_Post;
use WP_Query;
use ArrayAccess;

/**
 * @template T of PostModel
 * @template-extends ArrayAccess<array-key|null, T>
 */
class PostsCollection extends Collection
{
    protected $query = null;

    /**
     * @throws OffbeatCollectionException
     * @var int[]|WP_Post[]|WP_Query $items
     */
    public function __construct($items = []) {
        $postItems = [];

        if ($items instanceof WP_Query) {
            $this->query = $items;

            if (!empty($items->posts)) {
                foreach ($items->posts as $post) {
                    $postItems[] = offbeat('post')->convertWpPostToModel($post);
                }
            }
        } elseif (is_iterable($items)) {
            foreach ($items as $key => $item) {
                if ($item instanceof WP_Post) {
                    $postItems[$key] = $this->createValidPostModel($item);
                }
            }
        }

        parent::__construct($postItems);
    }

    public function getIterator(): WpPostsIterator {
        return new WpPostsIterator($this->items);
    }

    public function getQuery() {
        return $this->query;
    }

    /**
     * Retrieves all object Ids within this collection as an array
     * @return int[]
     */
    public function getIds(): array {
        return array_map(static function (PostModel $model) {
            return $model->getId() ?: 0;
        }, $this->items);
    }

    /** Returns this PostsCollection as a generic Collection */
    public function toCollection(): Collection {
        return collect($this->toArray());
    }

    public function map(callable $callback): Collection {
        $keys = array_keys($this->items);
        $items = array_map($callback, $this->items, $keys);

        return new Collection(array_combine($keys, $items));
    }

    /** @return T|PostModel|mixed */
    public function first(callable $callback = null, $default = null)
    {
        return parent::first($callback, $default);
    }

    /** @return T|PostModel|mixed */
    public function last(callable $callback = null, $default = null)
    {
        return parent::last($callback, $default);
    }

    /** @return T|PostModel|static|null */
    public function pop($count = 1)
    {
        return parent::pop($count);
    }

    /** @return T|PostModel|mixed */
    public function pull($key, $default = null)
    {
        return parent::pull($key, $default);
    }

    /** @return T|PostModel|null */
    public function reduce(callable $callback, $initial = null)
    {
        return parent::reduce($callback, $initial);
    }

    /** @return T|PostModel|static|null */
    public function shift($count = 1)
    {
        return parent::shift($count);
    }

    /**
     * @param int|WP_Post|PostModel $item
     * @throws OffbeatCollectionException
     */
    private function createValidPostModel($item): PostModel
    {
        $model = null;

        if (is_int($item) || $item instanceof WP_Post) {
            $model = offbeat('post')->get($item);
        } elseif ($item instanceof PostModel) {
            $model = $item;
        }

        if (!$model || !$model->wpPost) {
            throw new OffbeatCollectionException('Valid PostsCollection could not be created with passed items.');
        }

        return $model;
    }
}
