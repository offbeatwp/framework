<?php

namespace OffbeatWP\Content\Common;

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
    protected readonly array $items;

    /** @param array<int, mixed> $items */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /** Get all of the items in the collection as an array. */
    final public function all(): array
    {
        return $this->items;
    }

    /**
     * Determine if an item exists in the collection by key.
     * @param int|int[] $offset
     */
    final public function has(int|array $offset): bool
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
    final public function isEmpty(): bool
    {
        return !$this->items;
    }

    /**
     * Get the keys of the collection items.
     * @return int[]
     */
    final public function keys(): array
    {
        return array_keys($this->items);
    }

    /** Get the first item from the collection. */
    public function first()
    {
        return $this->items[0] ?? null;
    }

    /** Get the last item from the collection. */
    public function last()
    {
        return $this->items[count($this->items) - 1] ?? null;
    }

    /** Count the number of items in the collection. */
    final public function count(): int
    {
        return count($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     * @param int $offset
     */
    final public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Get an item at a given offset.
     * @param int $offset
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    /**
     * @throws BadMethodCallException
     * @return never-return
     */
    final public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException('Cannot set value on ReadOnlyCollection.');
    }

    /**
     * @throws BadMethodCallException
     * @return never-return
     */
    final public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException('Cannot unset value on ReadOnlyCollection.');
    }

    final public function toArray(): array
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

    public function jsonSerialize(): array
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

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}
