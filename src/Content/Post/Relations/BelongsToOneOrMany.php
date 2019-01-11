<?php
namespace OffbeatWP\Content\Post\Relations;

class BelongsToOneOrMany extends Relation
{
    public function associate($ids)
    {
        if (is_array($ids)) {
            $this->makeRelationships($ids, 'reverse');
        } else {
            $this->makeRelationship($ids, 'reverse');
        }
    }

    public function dissociate()
    {
        $this->removeRelationship('reverse');
    }
}
