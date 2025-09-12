<?php

namespace OffbeatWP\Content\User;

use OffbeatWP\Content\Common\ReadonlyCollection;

/**
 * @template TKey of int
 * @template TValue of UserModel
 * @extends ReadonlyCollection<TKey, TValue>
 */
final class UserCollection extends ReadonlyCollection
{
    /**
     * @param list<\WP_User> $items
     * @param class-string<TValue> $modelClass
     */
    public function __construct(array $items, string $modelClass = UserModel::class)
    {
        parent::__construct($items, $modelClass);
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
