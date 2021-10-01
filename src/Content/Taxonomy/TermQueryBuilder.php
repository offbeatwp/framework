<?php

namespace OffbeatWP\Content\Taxonomy;

use OffbeatWP\Content\AbstractQueryBuilder;
use OffbeatWP\Exceptions\TermModelNotFoundException;
use WP_Term_Query;

class TermQueryBuilder extends AbstractQueryBuilder
{
    protected $model;
    protected $taxonomy;

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

    // Retrieval methods
    public function get(): TermsCollection
    {
        $termModels = new TermsCollection();
        $terms = (new WP_Term_Query($this->queryVars))->get_terms();

        if (!empty($terms)) {
            foreach ($terms as $term) {
                $termModels->push(new $this->model($term));
            }
        }

        return $termModels;
    }

    public function all(): TermsCollection
    {
        $this->queryVars['number'] = 0;

        return $this->get();
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

    /** @throws TermModelNotFoundException */
    public function firstOrFail(): TermModel
    {
        $result = $this->first();

        if (!$result) {
            throw new TermModelNotFoundException('The query did not return any TermModels');
        }

        return $result;
    }

    public function findById(int $id): ?TermModel
    {
        return $this->findBy('id', $id);
    }

    /** @throws TermModelNotFoundException */
    public function findByIdorFail(int $id): ?TermModel
    {
        return $this->findOrFail('id', $id);
    }

    public function findBySlug(string $slug): ?TermModel
    {
        return $this->findBy('slug', $slug);
    }

    /** @throws TermModelNotFoundException */
    public function findBySlugOrFail(string $slug): ?TermModel
    {
        return $this->findOrFail('slug', $slug);
    }

    public function findByName(string $name): ?TermModel
    {
        return $this->findBy('name', $name);
    }

    /** @throws TermModelNotFoundException */
    public function findByNameOrFail(string $name): TermModel
    {
        return $this->findOrFail('name', $name);
    }

    public function findBy(string $field, $value): ?TermModel
    {
        $term = get_term_by($field, $value, $this->taxonomy);

        return empty($term) ? null : new $this->model($term);
    }

    /** @throws TermModelNotFoundException */
    public function findOrFail(string $field, $value): TermModel
    {
        $result = $this->findBy($field, $value);

        if (!$result) {
            throw new TermModelNotFoundException('Could not find term model where ' . $field . ' has a value of ' . $value);
        }

        return $result;
    }

    // Chainable methods
    public function whereParent(int $parentId): TermQueryBuilder
    {
        $this->queryVars['parent'] = $parentId;

        return $this;
    }

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
