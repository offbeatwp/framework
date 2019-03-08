<?php
namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\WpQueryBuilder;

class HasMany extends HasOneOrMany
{
    public function get()
    {
        return (new WpQueryBuilder())
            ->wherePostType('any')
            ->hasRelationshipWith($this->model, $this->key)
            ->all();
    }
}
