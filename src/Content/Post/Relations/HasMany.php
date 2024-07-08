<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\WpQueryBuilder;

class HasMany extends HasOneOrMany
{
    /** @return \OffbeatWP\Content\Post\WpQueryBuilder<\OffbeatWP\Content\Post\PostModel> */
    public function query()
    {
        return (new WpQueryBuilder())
            ->where(['ignore_sticky_posts' => 1])
            ->wherePostType('any')
            ->hasRelationshipWith($this->model, $this->relationKey);
    }

    /** @return \OffbeatWP\Content\Post\PostsCollection<int, \OffbeatWP\Content\Post\PostModel> */
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
