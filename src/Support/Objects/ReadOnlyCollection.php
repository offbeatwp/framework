<?php

namespace Offbeatp\Support\Objects\ReadOnlyCollection;

use ArrayAccess;
use ArrayIterator;
use BadMethodCallException;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Traits\EnumeratesValues;
use IteratorAggregate;
use JsonSerializable;

class ReadOnlyCollection implements ArrayAccess, Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable
{
    use EnumeratesValues;

    protected array $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /** Get all of the items in the collection as an array. */
    public function all(): array
    {
        return $this->items;
    }

    /** Get an item from the collection by key. */
    public function get(int $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    /**
     * Determine if an item exists in the collection by key.
     *
     * @param int|int[] $offset
     * @return bool
     */
    public function has(int|array $offset): bool
    {
        $offsets = is_array($offset) ? $offset : func_get_args();

        foreach ($offsets as $value) {
            if (!array_key_exists($value, $this->items)) {
                return false;
            }
        }

        return true;
    }

    /** Determine if any of the keys exist in the collection. */
    public function hasAny(int|array $offset): bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        $offsets = is_array($offset) ? $offset : func_get_args();

        foreach ($offsets as $value) {
            if ($this->has($value)) {
                return true;
            }
        }

        return false;
    }

    /** Determine if the collection is empty or not. */
    public function isEmpty(): bool
    {
        return !$this->items;
    }

    /** Determine if the collection contains a single item. */
    public function containsOneItem(): bool
    {
        return $this->count() === 1;
    }

    /**
     * Get the keys of the collection items.
     * @return int[]
     */
    public function keys(): array
    {
        return array_keys($this->items);
    }

    /** Get the last item from the collection. */
    public function last(): mixed
    {
        return $this->items[count($this->items) - 1] ?? null;
    }

    /** Get an iterator for the items. */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /** Count the number of items in the collection. */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     * @param int $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Get an item at a given offset.
     * @param int $offset
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    /**
     * Set the item at a given offset.
     * @param int $offset
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException('Cannot modify readonly Collection.');
    }

    /**
     * Unset the item at a given offset.
     * @param int $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException('Cannot modify readonly Collection.');
    }
}
