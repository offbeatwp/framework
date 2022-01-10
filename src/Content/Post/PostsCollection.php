<?php
namespace OffbeatWP\Content\Post;

use OffbeatWP\Content\Common\OffbeatModelCollection;
use TypeError;
use WP_Post;
use WP_Query;

/**
 * @method PostModel|mixed pull($key, $default = null)
 * @method PostModel|mixed first(callable $callback = null, $default = null)
 * @method PostModel|mixed last(callable $callback = null, $default = null)
 * @method PostModel|static|null pop(int $count = 1)
 * @method PostModel|static|null shift(int $count = 1)
 * @method PostModel|null reduce(callable $callback, $initial = null)
 * @method PostModel offsetGet($key)
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

    /** @return WpPostsIterator|PostModel[] */
    public function getIterator(): WpPostsIterator {
        return new WpPostsIterator($this->items);
    }

    public function getQuery() {
        return $this->query;
    }

    /**
     * Retrieves all object Ids within this collection as an array.
     * @return int[]
     */
    public function getIds(): array {
        return array_map(static function (PostModel $model) {
            return $model->getId() ?: 0;
        }, $this->items);
    }

    /** @return PostModel[] */
    public function toArray()
    {
        return $this->toCollection()->toArray();
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
