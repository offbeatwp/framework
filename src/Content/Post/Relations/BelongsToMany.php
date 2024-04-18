<?php
namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\PostModelAbstract;
use OffbeatWP\Content\Post\PostCollection;
use OffbeatWP\Content\Post\PostQueryBuilder;

final class BelongsToMany extends BelongsToOneOrMany
{
    public function query(): PostQueryBuilder
    {
        return (new PostQueryBuilder(PostModelAbstract::class))
            ->where(['ignore_sticky_posts' => 1])
            ->hasRelationshipWith($this->model, $this->relationKey, true);
    }

    public function get(): PostCollection
    {
        return $this->query()->get();
    }
}
