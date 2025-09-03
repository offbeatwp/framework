<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Content\Post\WpQueryBuilder;

final class HasOne extends HasOneOrMany
{
    /** @return WpQueryBuilder<PostModel> */
    public function query(): WpQueryBuilder
    {
        return (new WpQueryBuilder())
             ->where(['ignore_sticky_posts' => 1])
             ->wherePostType('any')
             ->hasRelationshipWith($this->model, $this->relationKey);
    }

    public function get(): ?PostModel
    {
        return $this->query()->first();
    }
}
