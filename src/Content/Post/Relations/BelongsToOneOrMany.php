<?php
namespace OffbeatWP\Content\Post\Relations;

class BelongsToOneOrMany extends AbstractRelation
{
    /** @param int|int[] $ids */
    public function associate(int|array $ids, bool $append = true): void
    {
        if (!$append) {
            $this->dissociateAll();
        }
        
        if (is_array($ids)) {
            $this->makeRelationships($ids, true);
        } else {
            $this->makeRelationship($ids, true);
        }
    }

    public function dissociate(int $id): void
    {
        $this->removeRelationship($id, true);
    }

    public function dissociateAll(): void
    {
        $this->removeAllRelationships(true);
    }
}
