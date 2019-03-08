<?php
namespace OffbeatWP\Content\Post\Relations;

use OffbeatWP\Content\Post\WpQueryBuilder;

class BelongsTo extends BelongsToOneOrMany
{
    public function get()
    {
        return (new WpQueryBuilder())
            ->wherePostType('any')
            ->hasRelationshipWith($this->model, $this->key, 'reverse')
            ->first();
    }
}
