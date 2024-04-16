<?php

namespace OffbeatWP\Content\Traits;

use OffbeatWP\Exceptions\OffbeatModelNotFoundException;

trait BaseModelTrait
{
    public static function find(?int $id): ?static
    {
        return ($id) ? static::query()->findById($id) : null;
    }

    public static function first(): ?static
    {
        return static::query()->first();
    }

    public static function findOrNew(?int $id): ?static
    {
        return static::find($id) ?: static::create();
    }

    final public static function findOrFail(int $id): ?static
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

    /** @return static[] */
    final public static function allAsArray(): array
    {
        return static::all()->toArray();
    }

    final public static function create(): static
    {
        return new static();
    }
}