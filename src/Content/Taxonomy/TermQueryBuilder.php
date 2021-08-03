<?php

namespace OffbeatWP\Content\Taxonomy;

use OffbeatWP\Content\AbstractQueryBuilder;
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

    public function take($numberOfItems): TermsCollection
    {
        $this->queryVars['number'] = $numberOfItems;

        return $this->get();
    }

    public function first(): ?TermModel
    {
        return $this->take(1)->first();
    }

    public function findById($id)
    {
        return $this->findBy('id', $id);
    }

    public function findBySlug($slug)
    {
        return $this->findBy('slug', $slug);
    }

    public function findByName($name)
    {
        return $this->findBy('name', $name);
    }

    /** @return TermModel|false */
    public function findBy($field, $value)
    {
        $term = get_term_by($field, $value, $this->taxonomy);

        if (!empty($term)) {
            $term = new $this->model($term);
        }

        return $term;
    }

    // Chainable methods
    public function whereParent(int $parentId): TermQueryBuilder
    {
        $this->queryVars['parent'] = $parentId;

        return $this;
    }

    public function whereMeta($key, $value = '', $compare = '='): TermQueryBuilder
    {
        if (!isset($this->queryVars['meta_query'])) {
            $this->queryVars['meta_query'] = [];
        }

        $parameters = $key;

        if (!is_array($parameters)) {
            $parameters = [
                'key' => $key,
                'value' => $value,
                'compare' => $compare,
            ];
        }

        $this->queryVars['meta_query'][] = $parameters;

        return $this;
    }

    public function whereRelatedToPost($postIds): TermQueryBuilder
    {
        $this->queryVars['object_ids'] = $postIds;

        return $this;
    }

    public function excludeEmpty($hideEmpty = true): TermQueryBuilder
    {
        $this->queryVars['hide_empty'] = $hideEmpty;

        return $this;
    }
}
