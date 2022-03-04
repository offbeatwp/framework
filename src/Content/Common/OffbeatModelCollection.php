<?php

namespace OffbeatWP\Content\Common;

use Illuminate\Support\Collection;
use InvalidArgumentException;

abstract class OffbeatModelCollection extends Collection
{
    /**
     * Push one or more items onto the end of the collection.
     * @param int|object ...$values
     * @return static
     *
     */
    public function push(...$values)
    {
        $models = [];

        foreach ($values as $value) {
            $models[] = $this->convertToModelOrFail($value);
        }

        return parent::push(...$models);
    }

    /**
     * Push a model onto the beginning of the user collection.
     * @param int|object $value
     * @param array-key $key
     * @return static
     */
    public function prepend($value, $key = null)
    {
        return parent::prepend($this->convertToModelOrFail($value), $key);
    }

    /**
     * Add a model to the user collection.
     * @param int|object $item
     * @return static
     */
    public function add($item)
    {
        return parent::add($this->convertToModelOrFail($item));
    }

    /**
     * Set the model given the offset.
     * @param array-key $key
     * @param int|object $value
     */
    public function offsetSet($key, $value)
    {
        parent::offsetSet($key, $this->convertToModelOrFail($value));
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

    /** @return AbstractOffbeatModel[] */
    public function toArray()
    {
        return $this->toCollection()->toArray();
    }

    //////////////////
    /// Extensions ///
    //////////////////
    /** Convert this typed collection to a basic Collection */
    public function toCollection(): Collection
    {
        return collect($this->items);
    }

    /**
     * Retrieves all object Ids within this collection as an array.
     * @return int[]
     */
    public function getIds(): array
    {
        return array_map(static fn(AbstractOffbeatModel $model) => $model->getId() ?: 0, $this->items);
    }

    /**
     * @param int|object $item
     * @return AbstractOffbeatModel|null
     */
    abstract protected function convertToModel($item): ?AbstractOffbeatModel;

    /**
     * @param int|object $item
     * @return AbstractOffbeatModel
     */
    protected function convertToModelOrFail($item): AbstractOffbeatModel
    {
        $model = $this->convertToModel($item);

        if (!$model) {
            throw new InvalidArgumentException('Could not convert passed value to a model.');
        }

        return $model;
    }
}