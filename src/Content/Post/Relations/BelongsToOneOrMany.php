<?php

namespace OffbeatWP\Content\Post\Relations;

class BelongsToOneOrMany extends Relation
{
    /** @param int|int[] $ids */
    public function attach(int|array $ids, bool $append = true): void
    {
        if (!$append) {
            $this->detachAll();
        }

        if (is_array($ids)) {
            $this->makeRelationships($ids, 'reverse');
        } else {
            $this->makeRelationship($ids, 'reverse');
        }
    }

    public function detach(int $id): void
    {
        $this->removeRelationship($id, 'reverse');
    }

    public function detachAll(): void
    {
        $this->removeAllRelationships('reverse');
    }
}
