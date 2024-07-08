<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\WpQueryBuilder;

/**
 * @template T of \OffbeatWP\Content\Post\PostModel
 * @extends \OffbeatWP\Content\Post\Relations\BelongsToOneOrMany<T>
 */
class BelongsToMany extends BelongsToOneOrMany
{
    /** @phpstan-return WpQueryBuilder<T> */
    public function query()
    {
        /** @var WpQueryBuilder<T> $builer */
        $builer = new WpQueryBuilder();

        return $builer
            ->where(['ignore_sticky_posts' => 1])
            ->wherePostType($this->model::POST_TYPE)
            ->hasRelationshipWith($this->model, $this->relationKey, 'reverse');
    }

    /**
     * @return \OffbeatWP\Content\Post\PostsCollection<int, \OffbeatWP\Content\Post\PostModel>
     * @phpstan-return \OffbeatWP\Content\Post\PostsCollection<int, \OffbeatWP\Content\Post\PostModel>
     */
    public function get()
    {
        return $this->query()->all();
    }

    public function count(): int
    {
        return $this->query()->count();
    }

    /** @return int[] */
    public function ids(): array
    {
        return $this->query()->ids();
    }
}
