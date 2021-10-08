<?php
namespace OffbeatWP\Content\Taxonomy;

use Illuminate\Support\Collection;
use OffbeatWP\Exceptions\TermCollectionException;
use WP_Term;

/** @template T */
class TermsCollection extends Collection
{
    /**
     * @param int[]|WP_Term[]|TermModel[] $items
     * @throws TermCollectionException
     */
    public function __construct($items = []) {
        $terms = [];

        foreach ($items as $item) {
            $term = null;

            if (is_int($item) || $item instanceof WP_Term) {
                $term = new TermModel($item);
            }

            if (!$term || !$term->wpTerm || !$term->getId()) {
                throw new TermCollectionException('Valid TermCollection could not be created passed items.');
            }

            $terms[] = $term;
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

    /** @return T|Collection<T>|null */
    public function pop($count = 1)
    {
        return parent::pop($count);
    }

    /** @return T|Collection<T>|null */
    public function pull($key, $default = null)
    {
        return parent::pull($key, $default);
    }

    /** @return T|Collection<T>|null */
    public function reduce(callable $callback, $initial = null)
    {
        return parent::reduce($callback, $initial);
    }

    /** @return T|Collection<T>|null */
    public function shift($count = 1)
    {
        return parent::shift($count);
    }
}
