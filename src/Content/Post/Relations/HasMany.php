<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\WpQueryBuilder;

/**
 * @template T of \OffbeatWP\Content\Post\PostModel
 * @extends \OffbeatWP\Content\Post\Relations\HasOneOrMany<T>
 */
class HasMany extends HasOneOrMany
{
    /** @return WpQueryBuilder<T> */
    public function query()
    {
        /** @var WpQueryBuilder<T> $builder */
        $builder = new WpQueryBuilder();

        return $builder
            ->where(['ignore_sticky_posts' => 1])
            ->wherePostType($this->modelClass::POST_TYPE)
            ->hasRelationshipWith($this->model, $this->relationKey);
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
