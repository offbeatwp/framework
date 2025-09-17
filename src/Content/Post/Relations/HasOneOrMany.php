<?php

namespace OffbeatWP\Content\Post\Relations;

abstract class HasOneOrMany extends Relation
{
    /** @param int|int[] $ids */
    final public function attach(int|array $ids, bool $append = true): void
    {
        if (!$append) {
            $this->detachAll();
        }

        if (is_array($ids)) {
            $this->makeRelationships($ids);
        } else {
            $this->makeRelationship($ids);
        }
    }

    final public function detach(int $id): void
    {
        $this->removeRelationship($id);
    }

    final public function detachAll(): void
    {
        $this->removeAllRelationships();
    }
}
