<?php
namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Content\Post\PostCollection;
use OffbeatWP\Content\Post\WpQueryBuilder;

final class BelongsToMany extends BelongsToOneOrMany
{
    public function query(): WpQueryBuilder
    {
        return (new WpQueryBuilder(PostModel::class))
            ->where(['ignore_sticky_posts' => 1])
            ->hasRelationshipWith($this->model, $this->relationKey, true);
    }

    public function get(): PostCollection
    {
        return $this->query()->get();
    }
}
