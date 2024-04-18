<?php
namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Content\Post\PostCollection;
use OffbeatWP\Content\Post\PostQueryBuilder;

final class BelongsToMany extends BelongsToOneOrMany
{
    /** @return PostQueryBuilder<PostModel> */
    public function query(): PostQueryBuilder
    {
        return (new PostQueryBuilder(PostModel::class))
            ->where(['ignore_sticky_posts' => 1])
            ->hasRelationshipWith($this->model, $this->relationKey, true);
    }

    /** @return PostCollection<PostModel> */
    public function get(): PostCollection
    {
        return $this->query()->get();
    }
}
