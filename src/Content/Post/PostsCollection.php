<?php

namespace OffbeatWP\Content\Post;

use OffbeatWP\Content\Common\ReadonlyCollection;
use WP_Query;

/**
 * @template TKey of int
 * @template TValue of \OffbeatWP\Content\Post\PostModel
 * @extends ReadonlyCollection<TKey, TValue>
 */
class PostsCollection extends ReadonlyCollection
{
    protected readonly WP_Query $query;

    /** @param class-string<TValue> $modelClass */
    final public function __construct(WP_Query $query, string $modelClass)
    {
        $this->query = $query;
        /** @var list<TValue> $posts */
        $posts = $this->query->posts;

        parent::__construct($posts, $modelClass);
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
     * @return non-negative-int[]
     */
    final public function getIds(): array
    {
        return array_map(static fn (PostModel $model) => $model->getId() ?: 0, $this->items);
    }
}
