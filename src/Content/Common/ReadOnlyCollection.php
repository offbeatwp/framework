<?php

namespace OffbeatWP\Content\Common;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use OffbeatWP\Content\Post\PostModel;
use OffbeatWP\Content\Taxonomy\TermModel;
use OffbeatWP\Content\User\UserModel;
use OffbeatWP\Exceptions\OffbeatInvalidModelException;

/**
 * @template TKey of array-key
 * @template TValue of PostModel|TermModel|UserModel
 * @implements ArrayAccess<TKey, TValue>
 * @implements IteratorAggregate<TKey, TValue>
 */
abstract readonly class ReadonlyCollection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /** @var array<TValue> */
    private array $items;

    /** @param array<TValue> $items */
    final public function __construct(array $items)
    {
        $this->items = $items;
    }

    final public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /** @phpstan-return TValue|null */
    final public function offsetGet(mixed $offset): PostModel|TermModel|UserModel|null
    {
        return $this->items[$offset] ?? null;
    }

    final public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new OffbeatInvalidModelException("ReadonlyCollection is read-only.");
    }

    final public function offsetUnset(mixed $offset): never
    {
        throw new OffbeatInvalidModelException("ReadonlyCollection is read-only.");
    }

    /** @return non-negative-int */
    final public function count(): int
    {
        return count($this->items);
    }

    /** @return ArrayIterator<TKey, TValue> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /** @return TValue[] */
    final public function jsonSerialize(): array
    {
        return $this->items;
    }

    /** @return TValue[] */
    final public function all(): array
    {
        return $this->items;
    }

    /** @phpstan-return TValue|null */
    final public function get(int $index): PostModel|TermModel|UserModel|null
    {
        return $this->items[$index] ?? null;
    }
}
