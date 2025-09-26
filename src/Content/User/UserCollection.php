<?php

namespace OffbeatWP\Content\User;

use OffbeatWP\Content\Common\OffbeatCollection;
use WP_User_Query;

/**
 * @template TKey of int
 * @template TValue of UserModel
 * @extends OffbeatCollection<TKey, TValue>
 */
final class UserCollection extends OffbeatCollection
{
    protected readonly WP_User_Query $query;

    /** @param class-string<TValue> $modelClass */
    public function __construct(WP_User_Query $query, string $modelClass = UserModel::class)
    {
        $this->query = $query;
        /** @var list<\WP_User> $items */
        $items = $this->query->get_results();

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
