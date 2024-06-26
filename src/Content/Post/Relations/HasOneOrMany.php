<?php

namespace OffbeatWP\Content\Post\Relations;

class HasOneOrMany extends Relation
{
    /**
     * @param int|int[] $ids
     * @param bool $append
     * @return void
     */
    public function attach($ids, $append = true)
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

    /** @return void */
    public function detach(int $id)
    {
        $this->removeRelationship($id);
    }

    /** @return void */
    public function detachAll()
    {
        $this->removeAllRelationships();
    }
}
