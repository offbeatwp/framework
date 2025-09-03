<?php

namespace OffbeatWP\Content\Taxonomy;

use OffbeatWP\Content\Common\ReadonlyCollection;

/**
 * @template TKey of int
 * @template TValue of TermModel
 * @extends ReadonlyCollection<TKey, TValue>
 */
final class TermsCollection extends ReadonlyCollection
{
    /**
     * @param list<TValue> $items
     * @param class-string<TValue> $modelClass
     */
    public function __construct(array $items, string $modelClass = TermModel::class)
    {
        parent::__construct($items, $modelClass);
    }

    /** @return TValue|null */
    public function offsetGet(mixed $offset): ?TermModel
    {
        return parent::offsetGet($offset);
    }

    /** @return TValue|null */
    public function first(): ?TermModel
    {
        return parent::first();
    }
}
