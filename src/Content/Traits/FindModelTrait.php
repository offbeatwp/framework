<?php

namespace OffbeatWP\Content\Traits;

use OffbeatWP\Exceptions\ModelNotFoundException;

trait FindModelTrait
{
    /** @return static|null */
    public function findById(int $id) {
        return static::query()->first();
    }

    /**
     * @throws ModelNotFoundException
     * @return static
     */
    public function findByIdOrFail(int $id) {
        $item = $this->find($id);
        if (!$item) {
            throw new ModelNotFoundException('Could not find ' . static::class . ' model with id ' . $id);
        }

        return $item;
    }

    /**
     * @param int $id
     * @return static
     */
    public function findByIdOrCreate(int $id) {
        return $this->find($id) ?: new static();
    }
}