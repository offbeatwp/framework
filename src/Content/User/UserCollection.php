<?php

namespace OffbeatWP\Content\User;

use Offbeatp\Support\Objects\ReadOnlyCollection;
use WP_User_Query;

final class UserCollection extends ReadOnlyCollection
{
    protected readonly WP_User_Query $query;

    public function __construct(WP_User_Query $query) {
        $this->query = $query;
        parent::__construct($query->get_results());
    }

    /**
     * @param int $offset
     * @phpstan-return TModel|null
     */
    public function offsetGet(mixed $offset): ?UserModel
    {
        return parent::offsetGet($offset);
    }

    /** Get the first item from the collection. */
    public function first(): ?UserModel
    {
        return parent::first();
    }

    /** Get the last item from the collection. */
    public function last(): ?UserModel
    {
        return parent::last();
    }
}