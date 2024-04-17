<?php
namespace OffbeatWP\Content\Post\Relations;

class BelongsToOneOrMany extends Relation
{
    /** @param int|int[] $ids */
    public function associate(int|array $ids, bool $append = true): void
    {
        if (!$append) {
            $this->dissociateAll();
        }
        
        if (is_array($ids)) {
            $this->makeRelationships($ids, 'reverse');
        } else {
            $this->makeRelationship($ids, 'reverse');
        }
    }

    public function dissociate(int $id)
    {
        $this->removeRelationship($id, 'reverse');
    }

    public function dissociateAll()
    {
        $this->removeAllRelationships('reverse');
    }
}
