<?php

namespace OffbeatWP\Content\Common;

use Illuminate\Support\Collection;

abstract class OffbeatModelCollection extends Collection
{
    /** Convert this typed collection to a basic Collection */
    public function toCollection(): Collection
    {
        return collect($this->items);
    }

    /**
     * Get the values of a given key. This will return a basic Collection.
     * @param string|array|int|null $value
     * @param string|null $key
     * @return Collection
     */
    public function pluck($value, $key = null)
    {
        return $this->toCollection()->pluck($value, $key);
    }

    /**
     * Get the keys of the collection items. This will return a basic Collection.
     * @return Collection<array-key>
     */
    public function keys()
    {
        return $this->toCollection()->keys();
    }

    /**
     * Run a map over each of the items.
     * @param callable $callback
     * @return Collection
     */
    public function map(callable $callback): Collection
    {
        return $this->toCollection()->map($callback);
    }
}