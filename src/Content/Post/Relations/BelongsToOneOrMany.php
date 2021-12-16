<?php
namespace OffbeatWP\Content\Post\Relations;

class BelongsToOneOrMany extends Relation
{
    /**
     * @param int[] $ids
     * @param bool $append
     */
    public function associate(iterable $ids, bool $append = true)
    {
        if (!$append) {
            $this->dissociateAll();
        }

        $this->makeRelationships($ids, 'reverse');
    }

    public function dissociate()
    {
        $this->removeRelationship('reverse');
    }

    public function dissociateAll()
    {
        $this->removeAllRelationships('reverse');
    }
}
