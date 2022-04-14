<?php

namespace OffbeatWP\Content\Post;

use OffbeatWP\Content\Common\OffbeatModelCollection;
use OffbeatWP\Contracts\IWpQuerySubstitute;
use TypeError;
use WP_Post;
use WP_Query;

/**
 * @method PostModel|mixed pull(int|string $key, mixed $default = null)
 * @method PostModel|mixed first(callable $callback = null, mixed $default = null)
 * @method PostModel|mixed last(callable $callback = null, mixed $default = null)
 * @method PostModel|static|null pop(int $count = 1)
 * @method PostModel|static|null shift(int $count = 1)
 * @method PostModel|null reduce(callable $callback, mixed $initial = null)
 * @method PostModel offsetGet(int|string $key)
 */
class PostsCollection extends OffbeatModelCollection
{
    protected $query = null;

    /** @var int[]|WP_Post[]|WP_Query $items */
    public function __construct($items = [])
    {
        $postItems = [];

        if ($items instanceof WP_Query || $items instanceof IWpQuerySubstitute) {
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

    /** @return WpPostsIterator|PostModel[] */
    public function getIterator(): WpPostsIterator
    {
        return new WpPostsIterator($this->items);
    }

    /**
     * Retrieves a paginated navigation to next/previous set of posts, when applicable.
     * @see paginate_links().
     */
    public function getPagination(array $args = []): string
    {
        if ($this->query instanceof WP_Query) {
            $GLOBALS['wp_query'] = $this->query;
            $paginationHtml = get_the_posts_pagination($args);
            wp_reset_query();

            return $paginationHtml;
        }

        return '';
    }

    /** @return IWpQuerySubstitute|WP_Query|null */
    public function getQuery()
    {
        return $this->query;
    }

    public function foundPosts(): int
    {
        return empty($this->query->found_posts) ? $this->count() : $this->query->found_posts;
    }

    /**
     * Retrieves all object Ids within this collection as an array.
     * @return int[]
     */
    public function getIds(): array
    {
        return array_map(static function (PostModel $model) {
            return $model->getId() ?: 0;
        }, $this->items);
    }

    /** @return PostModel[] */
    public function toArray()
    {
        return $this->toCollection()->toArray();
    }

    /**
     * Deletes <b>all</b> the items in this collection.
     * @param bool $force
     */
    public function deleteAll(bool $force)
    {
        $this->each(function (PostModel $model) use ($force) {
            $model->delete($force);
        });

        $this->items = [];
    }
}
