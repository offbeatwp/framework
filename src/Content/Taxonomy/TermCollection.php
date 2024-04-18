<?php
namespace OffbeatWP\Content\Taxonomy;

use OffbeatWP\Content\Common\ReadOnlyCollection;
use WP_Term_Query;

/**
 * @template TModel of \OffbeatWP\Content\Taxonomy\TermModel
 * @property \OffbeatWP\Content\Taxonomy\TermModel[] $items
 * @extends ReadOnlyCollection<TModel>
 */
final class TermCollection extends ReadOnlyCollection
{
    /** @var class-string<TModel> */
    protected readonly string $modelClass;
    protected readonly WP_Term_Query $query;

    /** @param class-string<TModel> $modelClass */
    public function __construct(WP_Term_Query $query, string $modelClass)
    {
        $this->query = $query;
        $this->modelClass = $modelClass;
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
        /** @phpstan-var TModel|null $item */
        $item = parent::offsetGet($offset);
        return $item;
    }

    /**
     * Get the first item from the collection.
     * @phpstan-return TModel|null
     */
    public function first(): ?TermModel
    {
        /** @phpstan-var TModel|null $item */
        $item = parent::first();
        return $item;
    }

    /**
     * Get the last item from the collection.
     * @phpstan-return TModel|null
     */
    public function last(): ?TermModel
    {
        /** @phpstan-var TModel|null $item */
        $item = parent::last();
        return $item;
    }
}
