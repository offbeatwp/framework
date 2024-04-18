<?php

namespace OffbeatWP\Content\User;

use OffbeatWP\Content\Common\ReadOnlyCollection;
use WP_User_Query;

/**
 * @template TModel of \OffbeatWP\Content\User\UserModel
 * @property \OffbeatWP\Content\User\UserModel[] $items
 */
final class UserCollection extends ReadOnlyCollection
{
    /** @var class-string<TModel> $modelClass */
    protected readonly string $modelClass;
    protected readonly WP_User_Query $query;

    /** @param class-string<TModel> $modelClass */
    public function __construct(WP_User_Query $query, string $modelClass)
    {
        $this->query = $query;
        $this->modelClass = $modelClass;

        $results = $query->get_results();
        parent::__construct($results);
    }

    /**
     * @param int $offset
     * @phpstan-return TModel|null
     */
    public function offsetGet(mixed $offset): ?UserModel
    {
        /** @var TModel|null $item */
        $item = parent::offsetGet($offset);
        return $item;
    }

    /**
     * Get the first item from the collection.
     * @phpstan-return TModel|null
     */
    public function first(): ?UserModel
    {
        /** @var TModel|null $item */
        $item = parent::first();
        return $item;
    }

    /**
     * Get the last item from the collection.
     * @phpstan-return TModel|null
     */
    public function last(): ?UserModel
    {
        /** @var TModel|null $item */
        $item = parent::last();
        return $item;
    }
}