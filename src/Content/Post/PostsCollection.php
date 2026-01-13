<?php

namespace OffbeatWP\Content\Post;

use OffbeatWP\Content\Common\OffbeatCollection;
use WP_Query;

/**
 * @template TKey of int
 * @template TValue of \OffbeatWP\Content\Post\PostModel
 * @extends OffbeatCollection<TKey, TValue>
 */
class PostsCollection extends OffbeatCollection
{
    protected readonly WP_Query $query;

    /** @param class-string<TValue> $modelClass */
    final public function __construct(WP_Query $query, string $modelClass)
    {
        $this->query = $query;
        /** @var list<\WP_Post> $items */
        $items = $this->query->get_posts();

        parent::__construct(array_map(fn ($v) => new $modelClass($v), $items), $modelClass);
    }

    /** @return \OffbeatWP\Content\Post\WpPostsIterator<array-key, TValue> */
    final public function getIterator(): WpPostsIterator
    {
        /** @var \OffbeatWP\Content\Post\WpPostsIterator<array-key, TValue> */
        return new WpPostsIterator($this->items);
    }

    final public function getQuery(): WP_Query
    {
        return $this->query;
    }

    /**
     * Retrieves all object Ids within this collection as an array.
     * @return list<positive-int>
     */
    final public function getIds(): array
    {
        /** @var list<positive-int> */
        return array_map(static fn (PostModel $model) => $model->getId(), $this->items);
    }
}
