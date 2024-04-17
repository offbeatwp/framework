<?php
namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Content\Post\WpQueryBuilder;

final class BelongsTo extends BelongsToOneOrMany
{
    public function query(): WpQueryBuilder
    {
        return (new WpQueryBuilder(PostModel::POST_TYPE))
            ->where(['ignore_sticky_posts' => 1])
            ->hasRelationshipWith($this->model, $this->relationKey, true);
    }

    public function get(): ?PostModel
    {
        return $this->query()->first();
    }
}
