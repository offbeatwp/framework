<?php

namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\WpQueryBuilder;

class BelongsToMany extends BelongsToOneOrMany
{
    /** @return \OffbeatWP\Content\Post\WpQueryBuilder */
    public function query()
    {
        return (new WpQueryBuilder())
            ->where(['ignore_sticky_posts' => 1])
            ->wherePostType('any')
            ->hasRelationshipWith($this->model, $this->relationKey, 'reverse');
    }

    /** @return \OffbeatWP\Content\Post\PostsCollection */
    public function get()
    {
        return $this->query()->all();
    }
}
