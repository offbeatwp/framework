<?php

namespace OffbeatWP\Content\Taxonomy;

use OffbeatWP\Content\Common\ReadOnlyCollection;

/**
 * @template TKey of int
 * @template TValue of TermModel
 * @extends ReadOnlyCollection<TKey, TValue>
 */
final class TermsCollection extends ReadOnlyCollection
{
    /**
     * @param list<\WP_Term> $items
     * @param class-string<TValue> $modelClass
     */
    public function __construct(array $items, string $modelClass = TermModel::class)
    {
        parent::__construct(array_map(fn ($v) => new $modelClass($v), $items), $modelClass);
    }

    /** @return TValue|null */
    final public function offsetGet(mixed $offset): ?TermModel
    {
        return parent::offsetGet($offset);
    }

    /** @return TValue|null */
    final public function first(): ?TermModel
    {
        return parent::first();
    }
}
