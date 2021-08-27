<?php
namespace OffbeatWP\Content\Taxonomy;

use Illuminate\Support\Collection;

/** @template T */
class TermsCollection extends Collection
{
    /** Returns this PostsCollection as a generic Collection */
    public function toCollection(): Collection {
        return collect($this->toArray());
    }

    public function map(callable $callback): Collection {
        $keys = array_keys($this->items);

        $items = array_map($callback, $this->items, $keys);

        return new Collection(array_combine($keys, $items));
    }

    /** @return T|null */
    public function first(callable $callback = null, $default = null): ?TermModel
    {
        return parent::first($callback, $default);
    }

    /** @return T|null */
    public function last(callable $callback = null, $default = null): ?TermModel
    {
        return parent::last($callback, $default);
    }

    /** @return T|null */
    public function pop($count = 1): ?TermModel
    {
        return parent::pop($count);
    }

    /** @return T|null */
    public function pull($key, $default = null): ?TermModel
    {
        return parent::pull($key, $default);
    }

    /** @return T|null */
    public function reduce(callable $callback, $initial = null): ?TermModel
    {
        return parent::reduce($callback, $initial);
    }

    /** @return T|null */
    public function shift($count = 1): ?TermModel
    {
        return parent::shift($count);
    }
}
