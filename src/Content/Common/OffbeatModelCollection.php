<?php

namespace OffbeatWP\Content\Common;

use Illuminate\Support\Collection;

/**
 * @template TKey of array-key
 * @template TValue
 * @extends Collection<TKey, TValue>
 */
abstract class OffbeatModelCollection extends Collection
{
    /**
     * Convert this typed collection to a basic Collection
     * @return Collection<TKey, TValue>
     */
    public function toCollection(): Collection
    {
        return collect($this->items);
    }

    /**
     * Get the values of a given key. This will return a basic Collection.
     * @param string|mixed[]|int|null $value
     * @param string|null $key
     * @return Collection<TKey, TValue>
     */
    public function pluck($value, $key = null)
    {
        return $this->toCollection()->pluck($value, $key);
    }

    /**
     * Get the keys of the collection items. This will return a basic Collection.
     * @return Collection<int, array-key>
     */
    public function keys()
    {
        return $this->toCollection()->keys();
    }

    /**
     * Run a map over each of the items.
     *
     * @template TMapValue
     *
     * @param  callable(TValue, TKey): TMapValue  $callback
     * @return Collection<TKey, TMapValue>
     */
    public function mapWithKeys(callable $callback)
    {
        return $this->toCollection()->mapWithKeys($callback);
    }

    /**
     * Run a map over each of the items.
     *
     * @template TMapValue
     *
     * @param  callable(TValue, TKey): TMapValue  $callback
     * @return Collection<TKey, TMapValue>
     */
    public function map(callable $callback): Collection
    {
        return $this->toCollection()->map($callback);
    }

    /**
     * Run a map using a property on the containing models. This will return a basic Collection.
     * @param string $methodName
     * @return Collection<TKey, mixed>
     */
    public function mapAs(string $methodName): Collection
    {
        return $this->toCollection()->map(fn ($item) => $item->$methodName());
    }

    /**
     * Group an associative array by a field or using a callback. This will return a basic Collection.
     * @param mixed[]|callable|string $groupBy
     * @param bool $preserveKeys
     * @return Collection<array-key, static<array-key, TValue>>
     */
    public function groupBy($groupBy, $preserveKeys = false): Collection
    {
        return $this->toCollection()->groupBy($groupBy, $preserveKeys);
    }
}
