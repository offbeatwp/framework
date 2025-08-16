<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\WpQueryBuilderModel;

/**
 * @template T of \OffbeatWP\Content\Post\PostModel
 * @extends \OffbeatWP\Content\Post\Relations\HasOneOrMany<T>
 */
class HasMany extends HasOneOrMany
{
    /** @return WpQueryBuilderModel<T> */
    public function query()
    {
        return (new WpQueryBuilderModel($this->modelClass))
            ->where(['ignore_sticky_posts' => 1])
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
