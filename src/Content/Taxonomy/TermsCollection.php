<?php
namespace OffbeatWP\Content\Taxonomy;

use Illuminate\Support\Collection;

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

    /** @return TermModel|null */
    public function first(callable $callback = null, $default = null)
    {
        return parent::first($callback, $default);
    }

    /** @return TermModel|null */
    public function last(callable $callback = null, $default = null)
    {
        return parent::last($callback, $default);
    }

    /** @return TermModel|null */
    public function pop()
    {
        return parent::pop();
    }

    /** @return TermModel|null */
    public function pull($key, $default = null)
    {
        return parent::pull($key, $default);
    }

    /** @return TermModel|null */
    public function reduce(callable $callback, $initial = null)
    {
        return parent::reduce($callback, $initial);
    }

    /** @return TermModel|null */
    public function shift()
    {
        return parent::shift();
    }
}
