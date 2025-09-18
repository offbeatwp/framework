<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Content\Post\WpQueryBuilder;

final class BelongsToOne extends BelongsToOneOrMany
{
    /** @return WpQueryBuilder<\OffbeatWP\Content\Post\PostModel> */
    public function query(): WpQueryBuilder
    {
        return (new WpQueryBuilder())
            ->ignoreStickyPosts()
            ->wherePostType('any')
            ->hasRelationshipWith($this->model, $this->relationKey, true);
    }

    public function get(): ?PostModel
    {
        return $this->query()->first();
    }
}
