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
     * Run an associative map over each of the items. This will return a basic Collection.
     * The callback should return an associative array with a single key/value pair.
     * @param callable  $callback
     * @return Collection
     */
    public function mapWithKeys(callable $callback)
    {
        return $this->toCollection()->mapWithKeys($callback);
    }

    /**
     * Run a map over each of the items. This will return a basic Collection.
     * @param callable $callback
     * @return Collection
     */
    public function map(callable $callback): Collection
    {
        return $this->toCollection()->map($callback);
    }

    /**
     * Run a map using a property on the containing models. This will return a basic Collection.
     * @param non-empty-string $methodName
     * @return Collection
     */
    public function mapAs(string $methodName): Collection
    {
        return $this->toCollection()->map(function ($item) use ($methodName) {
            return $item->$methodName();
        });
    }

    /**
     * Group an associative array by a field or using a callback. This will return a basic Collection.
     * @param array|callable|string $groupBy
     * @param bool $preserveKeys
     * @return Collection
     */
    public function groupBy($groupBy, $preserveKeys = false): Collection
    {
        return $this->toCollection()->groupBy($groupBy, $preserveKeys);
    }
}