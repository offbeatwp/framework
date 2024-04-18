<?php
namespace OffbeatWP\Content\Post\Relations;

class HasOneOrMany extends AbstractRelation
{
    /** @param int|int[] $ids */
    public function attach(int|array $ids, bool $append = true): void
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

    public function detach(int $id): void
    {
        $this->removeRelationship($id);
    }

    public function detachAll(): void
    {
        $this->removeAllRelationships();
    }
}
