<?php

namespace OffbeatWP\Content\Post\Relations;

/**
 * @template T of \OffbeatWP\Content\Post\PostModel
 * @extends \OffbeatWP\Content\Post\Relations\Relation<T>
 */
class BelongsToOneOrMany extends Relation
{
    /**
     * @param int|int[] $ids
     * @param bool $append
     * @return void
     */
    public function associate($ids, $append = true)
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

    /** @return void */
    public function dissociate(int $id)
    {
        $this->removeRelationship($id, 'reverse');
    }

    /** @return void */
    public function dissociateAll()
    {
        $this->removeAllRelationships('reverse');
    }
}
