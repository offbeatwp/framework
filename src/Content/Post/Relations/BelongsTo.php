<?php
namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\PostModelAbstract;
use OffbeatWP\Content\Post\PostQueryBuilder;

final class BelongsTo extends BelongsToOneOrMany
{
    public function query(): PostQueryBuilder
    {
        return (new PostQueryBuilder(PostModelAbstract::class))
            ->where(['ignore_sticky_posts' => 1])
            ->hasRelationshipWith($this->model, $this->relationKey, true);
    }

    public function get(): ?PostModelAbstract
    {
        return $this->query()->first();
    }
}
