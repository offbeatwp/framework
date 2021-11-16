<?php

namespace OffbeatWP\Content\Traits;

use OffbeatWP\Exceptions\ModelNotFoundException;

trait FindModelTrait
{
    /** @return static|null */
    public static function find(int $id) {
        return static::query()->first();
    }

    /**
     * @throws ModelNotFoundException
     * @return static
     */
    public static function findOrFail(int $id) {
        $item = static::find($id);
        if (!$item) {
            throw new ModelNotFoundException('Could not find ' . static::class . ' model with id ' . $id);
        }

        return $item;
    }

    /**
     * @param int $id
     * @return static
     */
    public static function findOrCreate(int $id) {
        return static::find($id) ?: new static();
    }
}