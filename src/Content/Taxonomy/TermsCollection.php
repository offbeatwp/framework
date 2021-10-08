<?php
namespace OffbeatWP\Content\Taxonomy;

use Illuminate\Support\Collection;
use OffbeatWP\Exceptions\TermsCollectionException;
use WP_Term;

/** @template T */
class TermsCollection extends Collection
{
    /**
     * @param int[]|WP_Term[]|TermModel[] $items
     * @throws TermsCollectionException
     */
    public function __construct(array $items = []) {
        $terms = [];

        foreach ($items as $item) {
            $terms[] = $this->createValidTermModel($item);
        }

        parent::__construct($terms);
    }

    /** Returns this TermsCollection as a generic Collection */
    public function toCollection(): Collection {
        return collect($this->toArray());
    }

    /** @return T|mixed */
    public function first(callable $callback = null, $default = null)
    {
        return parent::first($callback, $default);
    }

    /** @return T|mixed */
    public function last(callable $callback = null, $default = null)
    {
        return parent::last($callback, $default);
    }

    /** @return T|TermModel|Collection<T|TermModel> */
    public function pop($count = 1)
    {
        return parent::pop($count);
    }

    /** @return T|TermModel|Collection<T|TermModel>|null */
    public function pull($key, $default = null)
    {
        return parent::pull($key, $default);
    }

    /** @return T|TermModel|Collection<T|TermModel> */
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
            $model = new TermModel($item);
        } else if ($item instanceof TermModel) {
            $model = $item;
        }

        if (!$model || !$model->wpTerm) {
            throw new TermsCollectionException('Valid TermCollection could not be created with passed items.');
        }

        return $model;
    }
}
