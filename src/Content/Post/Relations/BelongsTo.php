<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Content\Post\WpQueryBuilder;

final class BelongsTo extends BelongsToOneOrMany
{
    /** @return WpQueryBuilder<\OffbeatWP\Content\Post\PostModel> */
    public function query(): WpQueryBuilder
    {
        return (new WpQueryBuilder())
            ->ignoreStickyPosts()
            ->wherePostType('any')
            ->hasRelationshipWith($this->model, $this->relationKey, 'reverse');
    }

    public function get(): ?PostModel
    {
        return $this->query()->first();
    }
}
