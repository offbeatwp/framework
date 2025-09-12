<?php

namespace OffbeatWP\Content\Common;

use ArrayAccess;
use ArrayIterator;
use BadMethodCallException;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Content\Taxonomy\TermModel;
use OffbeatWP\Content\User\UserModel;

/**
 * @template TKey of int
 * @template TValue of PostModel|TermModel|UserModel
 * @implements ArrayAccess<TKey, TValue>
 * @implements IteratorAggregate<TKey, TValue>
 */
abstract class ReadonlyCollection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /** @var list<TValue> */
    protected readonly array $items;
    /** @var class-string<TValue> */
    protected readonly string $modelClass;

    /**
     * @param list<TValue> $items
     * @param class-string<TValue> $modelClass
     */
    public function __construct(array $items, string $modelClass)
    {
        $this->items = array_map(fn ($item) => new static($item), $items);
        $this->modelClass = $modelClass;
    }

    final public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    /** @phpstan-return TValue|null */
    public function offsetGet(mixed $offset): PostModel|TermModel|UserModel|null
    {
        return $this->items[$offset] ?? null;
    }

    final public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new BadMethodCallException('Cannot set offset on read-only Collection.');
    }

    final public function offsetUnset(mixed $offset): never
    {
        throw new BadMethodCallException('Cannot unset offset on read-only Collection.');
    }

    /** @return non-negative-int */
    final public function count(): int
    {
        return count($this->items);
    }

    /** @return ArrayIterator<int, TValue> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /** @return list<TValue> */
    final public function jsonSerialize(): array
    {
        return $this->items;
    }

    /** @return list<TValue> */
    final public function all(): array
    {
        return $this->items;
    }

    /** @return list<TValue> */
    final public function toArray(): array
    {
        return $this->items;
    }

    public function first(): PostModel|TermModel|UserModel|null
    {
        return $this->items[array_key_first($this->items)];
    }
}
