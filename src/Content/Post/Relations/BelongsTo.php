<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\WpQueryBuilder;

class BelongsTo extends BelongsToOneOrMany
{
    /** @return WpQueryBuilder<\OffbeatWP\Content\Post\PostModel> */
    public function query()
    {
        return (new WpQueryBuilder())
            ->where(['ignore_sticky_posts' => 1])
            ->wherePostType('any')
            ->hasRelationshipWith($this->model, $this->relationKey, 'reverse');
    }

    /** @return \OffbeatWP\Content\Post\PostModel */
    public function get()
    {
        return $this->query()->first();
    }
}
