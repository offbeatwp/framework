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

    public static function exists(?int $id): bool
    {
        return static::query()->whereIdIn([$id])->exists();
    }

    /** @return static[] */
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