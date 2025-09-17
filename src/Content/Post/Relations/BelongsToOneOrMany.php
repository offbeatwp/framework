<?php

namespace OffbeatWP\Content\Post\Relations;

abstract class BelongsToOneOrMany extends Relation
{
    /** @param int|int[] $ids */
    final public function associate(int|array $ids, bool $append = true): void
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

    final public function dissociate(int $id): void
    {
        $this->removeRelationship($id, true);
    }

    final public function dissociateAll(): void
    {
        $this->removeAllRelationships(true);
    }
}
