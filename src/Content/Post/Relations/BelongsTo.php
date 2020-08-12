<?php
namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\WpQueryBuilder;

class BelongsTo extends BelongsToOneOrMany
{
    public function query()
    {
        return (new WpQueryBuilder())
            ->where(['ignore_sticky_posts' => 1])
            ->wherePostType('any')
            ->hasRelationshipWith($this->model, $this->key, 'reverse');
    }

    public function get()
    {
        return $this->query()->first();
    }
}
