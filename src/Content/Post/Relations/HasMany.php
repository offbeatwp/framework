<?php
namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Content\Post\PostCollection;
use OffbeatWP\Content\Post\WpQueryBuilder;

final class HasMany extends HasOneOrMany
{
    public function query(): WpQueryBuilder
    {
        return (new WpQueryBuilder(PostModel::class))
            ->where(['ignore_sticky_posts' => 1])
            ->hasRelationshipWith($this->model, $this->relationKey);
    }

    public function get(): PostCollection
    {
        return $this->query()->get();
    }
}
