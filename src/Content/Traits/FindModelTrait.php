<?php

namespace OffbeatWP\Content\Traits;

use OffbeatWP\Exceptions\OffbeatModelNotFoundException;

trait FindModelTrait
{
    /** @return static|null */
    public static function find(int $id) {
        return static::query()->first();
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
    public static function findOrCreate(int $id) {
        return static::find($id) ?: new static(null);
    }
}