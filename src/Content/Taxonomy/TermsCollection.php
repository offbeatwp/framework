<?php
namespace OffbeatWP\Content\Taxonomy;

use ArrayIterator;
use OffbeatWP\Content\Common\OffbeatModelCollection;
use TypeError;
use WP_Term;

/**
 * @method TermModel|mixed pull(int|string $key, $default = null)
 * @method TermModel|mixed first(callable $callback = null, mixed $default = null)
 * @method TermModel|mixed last(callable $callback = null, mixed $default = null)
 * @method TermModel|static|null pop(int $count = 1)
 * @method TermModel|static|null shift(int $count = 1)
 * @method TermModel|null reduce(callable $callback, mixed $initial = null)
 * @method TermModel offsetGet(int|string $key)
 * @method ArrayIterator|TermModel[] getIterator()
 */
class TermsCollection extends OffbeatModelCollection
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
     * @deprecated
     * Retrieves all object Ids within this collection as an array.
     * @return int[]
     */
    public function getIds(): array {
        return array_map(static function (TermModel $model) {
            return $model->getId() ?: 0;
        }, $this->items);
    }

    /** @return string[] Returns an array of term names indexed by their id. */
    public function getNames(): array
    {
        $names = [];

        $this->each(function (TermModel $model) use (&$names) {
            $names[$model->getId()] = $model->getName();
        });

        return $names;
    }

    /** @param int|WP_Term|TermModel $item */
    protected function createValidTermModel($item): ?TermModel
    {
        if ($item instanceof TermModel) {
            return $item;
        }

        if (is_int($item) || $item instanceof WP_Term) {
            return offbeat('taxonomy')->get($item);
        }

        throw new TypeError(gettype($item) . ' cannot be used to generate a TermModel.');
    }

    /**
     * Deletes <b>all</b> the terms in this collection from the database.
     * @param bool $force
     */
    public function deleteAll(bool $force)
    {
        $this->each(function (TermModel $model) {
            $model->delete();
        });

        $this->items = [];
    }
}
