<?php

namespace OffbeatWP\Content\Traits;

use OffbeatWP\Exceptions\OffbeatModelNotFoundException;

trait FindModelTrait
{
    /** @return static|null */
    public static function find(int $id) {
        return static::query()->findById($id);
    }

    /**
     * @return static
     * @throws OffbeatModelNotFoundException
     */
    public static function findOrFail(int $id) {
        $item = static::find($id);
        if (!$item) {
            throw new OffbeatModelNotFoundException('Could not find ' . static::class . ' model with id ' . $id);
        }

        return $item;
    }

    /**
     * @param int $id
     * @return static
     */
    public static function findOrNew(int $id) {
        return static::find($id) ?: new static(null);
    }
}