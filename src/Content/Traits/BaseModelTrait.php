<?php

namespace OffbeatWP\Content\Traits;

use OffbeatWP\Exceptions\OffbeatModelNotFoundException;

trait BaseModelTrait
{
    /** @return static|null */
    public static function find(int $id)
    {
        return static::query()->findById($id) ?: null;
    }

    /**
     * @throws OffbeatModelNotFoundException
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

    /** @return static */
    public static function findOrNew(int $id)
    {
        return static::find($id) ?: static::create();
    }

    /** @return static[] */
    public static function allAsArray()
    {
        return static::query()->all()->toArray();
    }

    /** @return static */
    public static function create()
    {
        return new static(null);
    }
}