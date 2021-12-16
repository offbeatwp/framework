<?php
namespace OffbeatWP\Content\Post\Relations;

class HasOneOrMany extends Relation
{
    /**
     * @param int[] $ids
     * @param bool $append
     */
    public function attach(iterable $ids, bool $append = true)
    {
        if (!$append) {
            $this->detachAll();
        }

        $this->makeRelationships($ids);
    }

    /** @param int $id */
    public function detach($id)
    {
        $this->removeRelationship($id);
    }

    public function detachAll()
    {
        $this->removeAllRelationships();
    }
}
