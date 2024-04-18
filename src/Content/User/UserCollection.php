<?php

namespace OffbeatWP\Content\User;

use OffbeatWP\Content\Common\ReadOnlyCollection;
use WP_User_Query;

/** @template TModel of \OffbeatWP\Content\User\UserModelAbstract */
final class UserCollection extends ReadOnlyCollection
{
    protected readonly WP_User_Query $query;
    /** @var class-string<TModel> $modelClass */
    protected readonly string $modelClass;

    /**
     * @param \WP_User_Query $query
     * @param class-string<TModel> $modelClass
     */
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
    public function offsetGet(mixed $offset): ?UserModelAbstract
    {
        /** @var TModel|null $item */
        $item = parent::offsetGet($offset);
        return $item;
    }

    /**
     * Get the first item from the collection.
     * @phpstan-return TModel|null
     */
    public function first(): ?UserModelAbstract
    {
        /** @var TModel|null $item */
        $item = parent::first();
        return $item;
    }

    /**
     * Get the last item from the collection.
     * @phpstan-return TModel|null
     */
    public function last(): ?UserModelAbstract
    {
        /** @var TModel|null $item */
        $item = parent::last();
        return $item;
    }
}