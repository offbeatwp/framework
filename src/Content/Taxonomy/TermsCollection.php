<?php

namespace OffbeatWP\Content\Taxonomy;

use OffbeatWP\Content\Common\ReadOnlyCollection;
use WP_Term_Query;

/**
 * @template TKey of int
 * @template TValue of TermModel
 * @extends ReadOnlyCollection<TKey, TValue>
 */
final class TermsCollection extends ReadOnlyCollection
{
    protected readonly WP_Term_Query $query;

    /** @param class-string<TValue> $modelClass */
    public function __construct(WP_Term_Query $query, string $modelClass = TermModel::class)
    {
        $this->query = $query;
        /** @var list<\WP_Term> $items */
        $items = $this->query->get_terms();

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
