<?php

namespace OffbeatWP\Content\Traits;

use OffbeatWP\Exceptions\OffbeatModelNotFoundException;

trait BaseModelTrait
{
    /** @return static|null */
    public static function find(?int $id)
    {
        return ($id) ? static::query()->findById($id) : null;
    }

    /** @return static|null */
    public static function first()
    {
        return static::query()->first();
    }

    /** @return static */
    public static function findOrNew(?int $id)
    {
        return static::find($id) ?: static::create();
    }

    /** @return static */
    public static function findOrFail(int $id)
    {
        $item = static::find($id);
        if (!$item) {
            throw new OffbeatModelNotFoundException('Could not find ' . static::class . ' model with id ' . $id);
        }

        return $item;
    }

    /**
     * Checks if a model with the given ID exists.
     * @param int|null $id
     * @return bool
     */
    public static function exists(?int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        return static::query()->whereIdIn([$id])->exists();
    }

    /** @return array<int, static> */
    public static function allAsArray()
    {
        return static::all()->toArray();
    }

    /** @return static */
    public static function create()
    {
        return new static(null);
    }
}
