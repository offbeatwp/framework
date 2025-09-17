<?php

namespace OffbeatWP\Content\Post\Relations;

class BelongsToOneOrMany extends Relation
{
    /** @param int|int[] $ids */
    public function attach(int|array $ids): void
    {
        $this->detachAll();

        if (is_array($ids)) {
            $this->makeRelationships($ids, true);
        } else {
            $this->makeRelationship($ids, true);
        }
    }

    public function detach(int $id): void
    {
        $this->removeRelationship($id, true);
    }

    public function detachAll(): void
    {
        $this->removeAllRelationships(true);
    }
}
