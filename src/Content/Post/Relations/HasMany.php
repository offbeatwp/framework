<?php
namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\WpQueryBuilder;

class HasMany extends HasOneOrMany
{
    public function query()
    {
        return (new WpQueryBuilder())
            ->wherePostType('any')
            ->hasRelationshipWith($this->model, $this->key);
    }

    public function get()
    {
        return $this->query()->all();
    }
}
