<?php
namespace OffbeatWP\Content\Taxonomy;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use TypeError;
use WP_Term;
use ArrayAccess;

/**
 * @template T of TermModel
 * @template-extends ArrayAccess<array-key|null, T>
 */
class TermsCollection extends Collection
{
    /** @param int[]|WP_Term[]|TermModel[] $items */
    public function __construct(iterable $items = []) {
        $terms = [];

        foreach ($items as $item) {
            $termModel = $this->createValidTermModel($item);
            if ($termModel) {
                $terms[] = $termModel;
            }
        }

        parent::__construct($terms);
    }

    /**
     * Retrieves all object Ids within this collection as an array
     * @return int[]
     */
    public function getIds(): array {
        return array_map(static function (TermModel $model) {
            return $model->getId() ?: 0;
        }, $this->items);
    }

    public function map(callable $callback): Collection {
        $keys = array_keys($this->items);
        $items = array_map($callback, $this->items, $keys);

        return new Collection(array_combine($keys, $items));
    }

    /** Returns this TermsCollection as a generic Collection */
    public function toCollection(): Collection {
        return collect($this->toArray());
    }

    /** @return T|TermModel|mixed */
    public function first(callable $callback = null, $default = null)
    {
        return parent::first($callback, $default);
    }

    /** @return T|TermModel|mixed */
    public function last(callable $callback = null, $default = null)
    {
        return parent::last($callback, $default);
    }

    /** @return T|TermModel|static|null */
    public function pop($count = 1)
    {
        return parent::pop($count);
    }

    /** @return T|TermModel|mixed */
    public function pull($key, $default = null)
    {
        return parent::pull($key, $default);
    }

    /** @return T|TermModel|static|null */
    public function shift($count = 1)
    {
        return parent::shift($count);
    }

    public function pluck($value, $key = null)
    {
        return new Collection(Arr::pluck($this->items, $value, $key));
    }

    /** @param int|WP_Term|TermModel $item */
    private function createValidTermModel($item): ?TermModel
    {
        if (is_int($item) || $item instanceof WP_Term) {
            $model = offbeat('taxonomy')->get($item);
        } elseif ($item instanceof TermModel) {
            $model = $item;
        } else {
            throw new TypeError(gettype($item) . ' cannot be used to generate a TermModel.');
        }

        return $model;
    }
}
