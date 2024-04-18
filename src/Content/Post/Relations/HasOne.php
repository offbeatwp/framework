<?php
namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\PostModelAbstract;
use OffbeatWP\Content\Post\PostQueryBuilder;

final class HasOne extends HasOneOrMany {

    public function query(): PostQueryBuilder
    {
       return (new PostQueryBuilder(PostModelAbstract::class))
            ->where(['ignore_sticky_posts' => 1])
            ->hasRelationshipWith($this->model, $this->relationKey);
    }

    public function get(): ?PostModelAbstract
    {
       return $this->query()->first();
    }
}
