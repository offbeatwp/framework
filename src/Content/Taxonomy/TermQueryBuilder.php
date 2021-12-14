<?php

namespace OffbeatWP\Content\Taxonomy;

use OffbeatWP\Content\AbstractQueryBuilder;
use OffbeatWP\Exceptions\OffbeatModelNotFoundException;
use WP_Term_Query;

class TermQueryBuilder extends AbstractQueryBuilder
{
    protected $model;
    protected $taxonomy;

    /** @param class-string<TermModel> $model */
    public function __construct($model)
    {
        $this->model = $model;
        $this->taxonomy = $model::TAXONOMY;

        $this->queryVars = [
            'taxonomy' => $model::TAXONOMY,
        ];

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

    /** @param int[] $ids Array of term IDs to exclude. If include is non-empty, exclude is ignored */
    public function include(array $ids) {
        $this->queryVars['include'] = $ids;
    }

    /** @param int[] $ids Array of term IDs to exclude. If include is non-empty, exclude is ignored */
    public function exclude(array $ids) {
        $this->queryVars['exclude'] = $ids;
    }

    /** @param int[] $ids Array of term IDs to exclude along with all of their descendant terms. If include is non-empty, excludeTree is ignored */
    public function excludeTree(array $ids) {
        $this->queryVars['exclude_tree'] = $ids;
    }

    /** @return TermsCollection<TermModel> */
    public function get(): TermsCollection
    {
        $termModels = new TermsCollection();
        $terms = (new WP_Term_Query($this->queryVars))->get_terms();

        foreach ($terms as $term) {
            $termModels->push(new $this->model($term));
        }

        return $termModels;
    }

    /** Keep in mind that empty terms are excluded by default. Set excludeEmpty to false to include empty terms */
    public function all(): TermsCollection
    {
        return $this->take(0);
    }

    public function take(int $numberOfItems): TermsCollection
    {
        $this->queryVars['number'] = $numberOfItems;

        return $this->get();
    }

    public function first(): ?TermModel
    {
        return $this->take(1)->first();
    }

    /** @throws OffbeatModelNotFoundException */
    public function firstOrFail(): TermModel
    {
        $result = $this->first();

        if (!$result) {
            throw new OffbeatModelNotFoundException('The query did not return any TermModels');
        }

        return $result;
    }

    public function findById(int $id)
    {
        return $this->findBy('id', $id);
    }

    /** @throws OffbeatModelNotFoundException */
    public function findByIdOrFail(int $id): TermModel
    {
        return $this->findByOrFail('id', $id);
    }

    public function findBySlug(string $slug)
    {
        return $this->findBy('slug', $slug);
    }

    /** @throws OffbeatModelNotFoundException */
    public function findBySlugOrFail(string $slug): TermModel
    {
        return $this->findByOrFail('slug', $slug);
    }

    public function findByName(string $name)
    {
        return $this->findBy('name', $name);
    }

    /** @throws OffbeatModelNotFoundException */
    public function findByNameOrFail(string $name): TermModel
    {
        return $this->findByOrFail('name', $name);
    }

    /**
     * @param string $field
     * @param string|int $value
     * @return TermModel|false|null
     */
    public function findBy(string $field, $value)
    {
        $term = get_term_by($field, $value, $this->taxonomy);

        return !$term ? $term : new $this->model($term);
    }

    /**
     * @throws OffbeatModelNotFoundException
     * @param string $field
     * @param string|int $value
     * @return TermModel
     */
    public function findByOrFail(string $field, $value): TermModel
    {
        $result = $this->findBy($field, $value);

        if (!$result) {
            throw new OffbeatModelNotFoundException('Could not find ' . static::class . ' where ' . $field . ' has a value of ' . $value);
        }

        return $result;
    }

    // Chainable methods
    public function whereParent(int $parentId): TermQueryBuilder
    {
        $this->queryVars['parent'] = $parentId;

        return $this;
    }

    /**
     * @param string|array $key Valid keys include 'key', 'value', 'compare' and 'type'
     * @param string|int|string[]|int[] $value
     * @param string $compare
     * @return $this
     */
    public function whereMeta($key, $value = '', string $compare = '='): TermQueryBuilder
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

    /** @param int|int[] $postIds */
    public function whereRelatedToPost($postIds): TermQueryBuilder
    {
        $this->queryVars['object_ids'] = $postIds;

        return $this;
    }

    public function excludeEmpty(bool $hideEmpty = true): TermQueryBuilder
    {
        $this->queryVars['hide_empty'] = $hideEmpty;

        return $this;
    }
}
