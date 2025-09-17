<?php

namespace OffbeatWP\Content\User;

use OffbeatWP\Content\Common\ReadOnlyCollection;

/**
 * @template TKey of int
 * @template TValue of UserModel
 * @extends ReadOnlyCollection<TKey, TValue>
 */
final class UserCollection extends ReadOnlyCollection
{
    /**
     * @param list<\WP_User> $items
     * @param class-string<TValue> $modelClass
     */
    public function __construct(array $items, string $modelClass = UserModel::class)
    {
        parent::__construct(array_map(fn ($v) => new $modelClass($v), $items), $modelClass);
    }

    /** @return TValue|null */
    final public function offsetGet(mixed $offset): ?UserModel
    {
        return parent::offsetGet($offset);
    }

    /** @return TValue|null */
    final public function first(): ?UserModel
    {
        return parent::first();
    }
}
