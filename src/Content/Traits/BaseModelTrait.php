<?php

namespace OffbeatWP\Content\Traits;

use OffbeatWP\Exceptions\OffbeatModelNotFoundException;

trait BaseModelTrait
{
    /**
     * Will retrieve a model from the database for the given ID, or <i>NULL</i> if it does not exist.<br>
     * If the given ID is a non-positive int or NULL then this method will immediately return <i>NULL</i> without performing a query.
     * @return static|null
     */
    public static function find(?int $id)
    {
        return static::query()->findById($id);
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

    /**
     * Will retrieve a model from the database for the given ID, or throw a <i>OffbeatModelNotFoundException</i> if no such model exists.<br>
     * If the given ID is a non-positive int then always throw an exception.
     * @throws \OffbeatWP\Exceptions\OffbeatModelNotFoundException
     * @param positive-int $id
     * @return static
     */
    public static function findOrFail(int $id)
    {
        $item = static::find($id);
        if (!$item) {
            throw new OffbeatModelNotFoundException('Could not find ' . static::class . ' model with id ' . $id);
        }

        return $item;
    }

    /** Checks if a model with the given ID exists. */
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
