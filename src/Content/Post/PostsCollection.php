<?php
namespace OffbeatWP\Content\Post;

use OffbeatWP\Content\Common\OffbeatModelCollection;
use TypeError;
use WP_Post;
use WP_Query;
use ArrayAccess;

/**
 * @template T of PostModel
 * @template-extends ArrayAccess<array-key|null, T>
 */
class PostsCollection extends OffbeatModelCollection
{
    protected $query = null;

    /** @var int[]|WP_Post[]|WP_Query $items */
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
                $postModel = $this->createValidPostModel($item);
                if ($postModel) {
                    $postItems[$key] = $postModel;
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

    /** @return PostModel[]|T[] */
    public function toArray()
    {
        return $this->toCollection()->toArray();
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

    /** @param int|WP_Post|PostModel $item */
    protected function createValidPostModel($item): ?PostModel
    {
        if ($item instanceof PostModel) {
            return $item;
        }

        if (is_int($item) || $item instanceof WP_Post) {
            return offbeat('post')->get($item);
        }

        throw new TypeError(gettype($item) . ' cannot be used to generate a PostModel.');
    }
}
