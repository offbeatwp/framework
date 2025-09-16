<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\PostsCollection;
use OffbeatWP\Content\Post\WpQueryBuilder;

class BelongsToMany extends BelongsToOneOrMany
{
    /** @return \OffbeatWP\Content\Post\WpQueryBuilder<\OffbeatWP\Content\Post\PostModel> */
    public function query(): WpQueryBuilder
    {
        return (new WpQueryBuilder())
            ->ignoreStickyPosts()
            ->wherePostType('any')
            ->hasRelationshipWith($this->model, $this->relationKey, 'reverse');
    }

    /** @return \OffbeatWP\Content\Post\PostsCollection<int, \OffbeatWP\Content\Post\PostModel> */
    public function get(): PostsCollection
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
