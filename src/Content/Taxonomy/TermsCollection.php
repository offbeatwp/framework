<?php
namespace OffbeatWP\Content\Taxonomy;

use Illuminate\Support\Collection;
use OffbeatWP\Exceptions\TermsCollectionException;
use WP_Term;

/** @template T of TermModel */
class TermsCollection extends Collection
{
    /**
     * @param int[]|WP_Term[]|TermModel[] $items
     * @throws TermsCollectionException
     */
    public function __construct(iterable $items = []) {
        $terms = [];

        foreach ($items as $item) {
            $terms[] = $this->createValidTermModel($item);
        }

        parent::__construct($terms);
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

    /**
     * @param int|WP_Term|TermModel $item
     * @throws TermsCollectionException
     */
    private function createValidTermModel($item): TermModel
    {
        $model = null;

        if (is_int($item) || $item instanceof WP_Term) {
            $model = offbeat('taxonomy')->get($item);
        } else if ($item instanceof TermModel) {
            $model = $item;
        }

        if (!$model || !$model->wpTerm) {
            throw new TermsCollectionException('Valid TermCollection could not be created with passed items.');
        }

        return $model;
    }
}
