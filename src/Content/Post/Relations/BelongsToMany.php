<?php
namespace OffbeatWP\Content\Post\Relations;

class BelongsToMany extends BelongsToOneOrMany
{
    public function get()
    {
        return (new WpQueryBuilder())
            ->wherePostType('any')
            ->hasRelationshipWith($this->model, $this->key, 'reverse')
            ->all();
    }

    public function dissociateAll()
    {
        $this->removeAllRelationships('reverse');
    }
}
