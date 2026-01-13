<?php

namespace OffbeatWP\Content\Post\Relations;

abstract class BelongsToOneOrMany extends Relation
{
    /** @inheritDoc */
    final public function attach(int|array $ids): void
    {
        $this->detachAll();

        if (is_array($ids)) {
            $this->makeRelationships($ids, true);
        } else {
            $this->makeRelationship($ids, true);
        }
    }

    final public function detach(int $id): void
    {
        $this->removeRelationship($id, true);
    }

    final public function detachAll(): void
    {
        $this->removeAllRelationships(true);
    }
}
