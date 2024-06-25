<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\WpQueryBuilder;

class BelongsToMany extends BelongsToOneOrMany
{
    /** @return \OffbeatWP\Content\Post\WpQueryBuilder */
    public function query()
    {
        return (new WpQueryBuilder())
            ->where(['ignore_sticky_posts' => 1])
            ->wherePostType('any')
            ->hasRelationshipWith($this->modelId, $this->relationKey, 'reverse');
    }

    /** @return \OffbeatWP\Content\Post\PostsCollection */
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
