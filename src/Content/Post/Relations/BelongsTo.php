<?php
namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Content\Post\PostQueryBuilder;

final class BelongsTo extends BelongsToOneOrMany
{
    /** @return PostQueryBuilder<PostModel> */
    public function query(): PostQueryBuilder
    {
        return (new PostQueryBuilder(PostModel::class))
            ->where(['ignore_sticky_posts' => 1])
            ->hasRelationshipWith($this->model, $this->relationKey, true);
    }

    public function get(): ?PostModel
    {
        return $this->query()->first();
    }
}
