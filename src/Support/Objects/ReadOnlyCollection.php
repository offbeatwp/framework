<?php

namespace Offbeatp\Support\Objects\ReadOnlyCollection;

use ArrayAccess;
use ArrayIterator;
use BadMethodCallException;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use IteratorAggregate;
use JsonSerializable;

abstract class ReadOnlyCollection implements ArrayAccess, Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable
{
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

    /** Determine if the collection is empty or not. */
    public function isEmpty(): bool
    {
        return !$this->items;
    }

    /**
     * Get the keys of the collection items.
     * @return int[]
     */
    public function keys(): array
    {
        return array_keys($this->items);
    }

    /** Get the first item from the collection. */
    public function first(): mixed
    {
        return $this->items[0] ?? null;
    }

    /** Get the last item from the collection. */
    public function last(): mixed
    {
        return $this->items[count($this->items) - 1] ?? null;
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
     * @throws BadMethodCallException
     * @return never-return
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException('Cannot modify readonly Collection.');
    }

    /**
     * @throws BadMethodCallException
     * @return never-return
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException('Cannot modify readonly Collection.');
    }

    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    public function jsonSerialize(): mixed
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            }

            if ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            }

            if ($value instanceof Arrayable) {
                return $value->toArray();
            }

            return $value;
        }, $this->all());
    }
}
