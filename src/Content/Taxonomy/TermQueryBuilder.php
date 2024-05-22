<?php

namespace OffbeatWP\Content\Taxonomy;

use InvalidArgumentException;
use OffbeatWP\Content\Traits\OffbeatQueryTrait;
use OffbeatWP\Exceptions\OffbeatModelNotFoundException;
use WP_Term_Query;

/** @template TModel of TermModel */
final class TermQueryBuilder
{
    use OffbeatQueryTrait;

    /** @var class-string<TModel> */
    protected string $model;
    protected array $queryVars = [];
    protected string $taxonomy;

    /** @param class-string<TModel> $model */
    public function __construct($model)
    {
        $this->model = $model;
        $this->taxonomy = $model::TAXONOMY;

        if ($this->taxonomy) {
            $this->queryVars['taxonomy'] = $this->taxonomy;
        }

        if (method_exists($model, 'defaultQuery')) {
            $model::defaultQuery($this);
        }

        $order = null;
        if (defined("$model::ORDER")) {
            $order = $model::ORDER;
        }

        $orderBy = null;
        if (defined("$model::ORDER_BY")) {
            $orderBy = $model::ORDER_BY;
        }

        $this->order($orderBy, $order);
    }

    /**
     * @param int[] $ids Array of term IDs to include.
     * @return $this
     */
    public function include(array $ids): self
    {
        $this->queryVars['include'] = $ids ?: [0];
        return $this;
    }

    /**
     * @param int[] $ids Array of term IDs to exclude.
     * @return $this
     */
    public function exclude(array $ids): self
    {
        $this->queryVars['exclude'] = $ids;
        return $this;
    }

    /**
     * @param int[] $ids Array of term IDs to exclude along with all of their descendant terms. If include is non-empty, excludeTree is ignored
     * @return $this
     */
    public function excludeTree(array $ids): self
    {
        $this->queryVars['exclude_tree'] = $ids;
        return $this;
    }

    /**
     * True to limit results to terms that have no children.<br>This parameter has no effect on non-hierarchical taxonomies.
     * @return $this
     */
    public function childless(bool $childless = true): self
    {
        $this->queryVars['childless'] = true;
        return $this;
    }

    /** @return TermsCollection<TModel> */
    public function get(): TermsCollection
    {
        $termModels = new TermsCollection();
        $terms = $this->runQuery()->get_terms();

        foreach ($terms as $term) {
            $termModels->push(new $this->model($term));
        }

        return $termModels;
    }

    /**
     * Keep in mind that empty terms are excluded by default. Set excludeEmpty to false to include empty terms
     * @return TermsCollection<TModel>
     */
    public function all(): TermsCollection
    {
        return $this->take(0);
    }

    /**
     * @param int $numberOfItems
     * @return TermsCollection<TModel>
     */
    public function take(int $numberOfItems): TermsCollection
    {
        $this->queryVars['number'] = $numberOfItems;
        return $this->get();
    }

    /**
     * @param int $amount
     * @return $this
     */
    public function limit(int $amount)
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("Limit expects a positive number, but received {$amount}.");
        }

        $this->queryVars['number'] = $amount;
        return $this;
    }

    /** @return int[] */
    public function ids(): array
    {
        $this->queryVars['number'] = $this->queryVars['number'] ?? 0;
        $this->queryVars['fields'] = 'ids';
        $this->queryVars['no_found_rows'] = true;

        return $this->runQuery()->get_terms();
    }

    /**
     * Returns an associative array of parent term IDs, keyed by term ID
     * @return int[]
     */
    public function parentIds(): array
    {
        $this->queryVars['number'] = $this->queryVars['number'] ?? 0;
        $this->queryVars['fields'] = 'id=>parent';
        $this->queryVars['no_found_rows'] = true;

        return $this->runQuery()->get_terms();
    }

    public function count(): int
    {
        $this->queryVars['number'] = $this->queryVars['number'] ?? 0;
        $this->queryVars['fields'] = 'count';
        $this->queryVars['no_found_rows'] = true;

        return (int)$this->runQuery()->get_terms();
    }

    /**
     * @param bool $indexById When true, the names will be indexed with their respective term ID.
     * @return string[]
     */
    public function names(bool $indexById): array
    {
        $this->queryVars['number'] = $this->queryVars['number'] ?? 0;
        $this->queryVars['fields'] = ($indexById) ? 'id=>name' : 'names';
        $this->queryVars['no_found_rows'] = true;

        return $this->runQuery()->get_terms();
    }

    public function firstName(): ?string
    {
        return $this->limit(1)->names(false)[0] ?? null;
    }

    /**
     * @param bool $indexById When true, the slugs will be indexed with their respective term ID.
     * @return string[]
     */
    public function slugs(bool $indexById): array
    {
        $this->queryVars['number'] = $this->queryVars['number'] ?? 0;
        $this->queryVars['fields'] = ($indexById) ? 'id=>slug' : 'slugs';
        $this->queryVars['no_found_rows'] = true;

        return $this->runQuery()->get_terms();
    }

    public function firstSlug(): ?string
    {
        return $this->limit(1)->slugs(false)[0] ?? null;
    }

    /** @phpstan-return TModel|null */
    public function first(): ?TermModel
    {
        return $this->take(1)->first();
    }

    /** @phpstan-return TModel */
    public function firstOrFail(): TermModel
    {
        $result = $this->first();

        if (!$result) {
            throw new OffbeatModelNotFoundException('The query did not return any TermModels');
        }

        return $result;
    }

    /** @phpstan-return TModel */
    public function firstOrNew(): TermModel
    {
        $result = $this->first();

        if (!$result) {
            $model = offbeat('taxonomy')->getModelByTaxonomy($this->taxonomy);
            return new $model(null);
        }

        return $result;
    }

    /** @phpstan-return TModel|null */
    private function _getTermById(?int $id): ?TermModel
    {
        if ($id <= 0) {
            return null;
        }

        $this->queryVars['term_taxonomy_id'] = [$id];
        return $this->first();
    }

    /** @phpstan-return TModel|null */
    public function findById(?int $id): ?TermModel
    {
        return $this->_getTermById($id);
    }

    /** @phpstan-return TModel */
    public function findByIdOrFail(int $id): TermModel
    {
        $result = $this->findById($id);

        if (!$result) {
            throw new OffbeatModelNotFoundException('Could not find ' . static::class . ' where id has a value of ' . $id);
        }

        return $result;
    }

    /** @phpstan-return TModel|null */
    public function findBySlug(string $slug): ?TermModel
    {
        return $this->findBy('slug', $slug);
    }

    /** @phpstan-return TModel */
    public function findBySlugOrFail(string $slug): TermModel
    {
        return $this->findByOrFail('slug', $slug);
    }

    /** @phpstan-return TModel|null */
    public function findByName(string $name): ?TermModel
    {
        return $this->findBy('name', $name);
    }

    /** @phpstan-return TModel */
    public function findByNameOrFail(string $name): TermModel
    {
        return $this->findByOrFail('name', $name);
    }

    /**
     * @param string $field Either 'slug', 'name', 'term_id' 'id', 'ID' or 'term_taxonomy_id'.
     * @param string|int $value
     * @phpstan-return TModel|null
     */
    public function findBy(string $field, string|int $value): ?TermModel
    {
        if ($field === 'id' || $field === 'ID' || $field === 'term_id') {
            return $this->_getTermById((int)$value);
        }

        if ($field === 'slug' || $field === 'name') {
            if (!$value) {
                return null;
            }

            $this->queryVars[$field] = $value;
        } elseif ($field === 'term_taxonomy_id') {
            $this->queryVars['term_taxonomy_id'] = $value;
            unset($this->queryVars['taxonomy']);
        } else {
            throw new InvalidArgumentException('TermQueryBuilder::findBy recieved invalid field value ' . $field);
        }

        return $this->first();
    }

    /**
     * @param string $field Either 'slug', 'name', 'term_id' 'id', 'ID' or 'term_taxonomy_id'.
     * @param string|int $value
     * @phpstan-return TModel
     */
    public function findByOrFail(string $field, $value): TermModel
    {
        $result = $this->findBy($field, $value);

        if (!$result) {
            throw new OffbeatModelNotFoundException('Could not find ' . static::class . ' where ' . $field . ' has a value of ' . $value);
        }

        return $result;
    }

    /**
     * @param string[] $slugs Array of slugs to return term(s) for.
     * @return $this
     */
    public function whereSlugIn(array $slugs): self
    {
        $this->queryVars['slug'] = $slugs;
        return $this;
    }

    /**
     * @param int $parentId
     * @return $this
     */
    public function whereParent(int $parentId): self
    {
        $this->queryVars['parent'] = $parentId;
        return $this;
    }

    /**
     * @param string|mixed[] $key Valid keys include 'key', 'value', 'compare' and 'type'
     * @param string|int|string[]|int[] $value
     * @param string $compare
     * @return $this
     */
    public function whereMeta($key, $value = '', string $compare = '='): self
    {
        if (!isset($this->queryVars['meta_query'])) {
            $this->queryVars['meta_query'] = [];
        }

        $parameters = $key;

        if (!is_array($parameters)) {
            $parameters = [
                'key' => $key,
                'value' => $value,
                'compare' => $compare
            ];
        }

        $this->queryVars['meta_query'][] = $parameters;

        return $this;
    }

    /**
     * @param int|int[]|null $postIds
     * @return $this
     */
    public function whereRelatedToPost($postIds): self
    {
        $this->queryVars['object_ids'] = $postIds ?: [0];
        return $this;
    }

    /** @return $this */
    public function excludeEmpty(bool $hideEmpty = true): self
    {
        $this->queryVars['hide_empty'] = $hideEmpty;
        return $this;
    }

    private function runQuery(): WP_Term_Query
    {
        // This is a fix for to ensure that passing an empty array to include returns no results.
        if (isset($this->queryVars['include']) && $this->queryVars['include'] === [0]) {
            $this->queryVars['object_ids'] = 0;
        }

        $query = new WP_Term_Query($this->queryVars);

        self::$lastRequest = $query->request;

        return $query;
    }
}
