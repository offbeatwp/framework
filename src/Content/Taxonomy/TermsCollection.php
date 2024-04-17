<?php
namespace OffbeatWP\Content\Taxonomy;

use OffbeatWP\Content\Common\ReadOnlyCollection;
use WP_Term_Query;

/**
 * @template TModel
 * @implements \ArrayAccess<int, TModel>
 */
final class TermsCollection extends ReadOnlyCollection
{
    protected readonly WP_Term_Query $query;

    public function __construct(WP_Term_Query $query)
    {
        $this->query = $query;
        parent::__construct($this->query->terms);
    }

    /** @return string[] Returns an array of term names indexed by their id. */
    public function getNames(): array
    {
        $names = [];

        foreach ($this->items as $model) {
            $names[$model->getId()] = $model->getName();
        }

        return $names;
    }

    /**
     * @param int $offset
     * @phpstan-return TModel|null
     */
    public function offsetGet(mixed $offset): ?TermModel
    {
        return parent::offsetGet($offset);
    }

    /** Get the first item from the collection. */
    public function first(): ?TermModel
    {
        return parent::first();
    }

    /** Get the last item from the collection. */
    public function last(): ?TermModel
    {
        return parent::last();
    }
}
