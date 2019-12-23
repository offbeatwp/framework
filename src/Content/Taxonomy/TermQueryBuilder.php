<?php
namespace OffbeatWP\Content\Taxonomy;

use OffbeatWP\Content\Taxonomy\TermsCollection;

class TermQueryBuilder
{
    protected $model;
    protected $taxonomy;
    protected $queryVars = [];

    public function __construct($model)
    {
        $this->model    = $model;
        $this->taxonomy = $model::TAXONOMY;

        $this->queryVars = [
            'taxonomy' => $model::TAXONOMY,
        ];

        if (method_exists($model, 'defaultQuery')) {
            $model::defaultQuery($this);
        }

        $order = null;
        if (defined("{$model}::ORDER")) {
            $order = $model::ORDER;
        }

        $orderBy = null;
        if (defined("{$model}::ORDER_BY")) {
            $orderBy = $model::ORDER_BY;
        }

        $this->order($orderBy, $order);
    }

    public function get()
    {
        $termModels = new TermsCollection();
        $terms      = (new \WP_Term_Query($this->queryVars))->get_terms();

        if (!empty($terms)) {
            foreach ($terms as $term) {
                $termModels->push(new $this->model($term));
            }
        }

        return $termModels;
    }

    public function all()
    {
        $this->queryVars['number'] = 0;
        
        return $this->get();
    }

    public function take($numberOfItems)
    {
        $this->queryVars['number'] = $numberOfItems;
        
        return $this->get();
    }

    public function first() {
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

    public function findBy($field, $value)
    {
        $term = get_term_by($field, $value, $this->taxonomy);

        if (!empty($term)) {
            $term = new $this->model($term);
        }

        return $term;
    }

    public function where($parameters)
    {
        $this->queryVars = array_merge($this->queryVars, $parameters);

        return $this;
    }

    public function whereMeta($key, $value = '', $compare = '=')
    {
        if (!isset($this->queryVars['meta_query'])) {
            $this->queryVars['meta_query'] = [];
        }

        $parameters = $key;

        if (!is_array($parameters)) {
            $parameters = [
                'key'     => $key,
                'value'   => $value,
                'compare' => $compare,
            ];
        }

        array_push($this->queryVars['meta_query'], $parameters);

        return $this;
    }

    public function whereRelatedToPost($postIds) {
        $this->queryVars['object_ids'] = $postIds;

        return $this;
    }

    public function excludeEmpty($hideEmpty = true) {
        $this->queryVars['hide_empty'] = $hideEmpty;

        return $this;
    }

    public function order($orderBy = null, $direction = null)
    {
        if (preg_match('/^(meta(_num)?):(.+)$/', $orderBy, $match)) {
            $this->queryVars['meta_key'] = $match[3];
            $this->queryVars['orderby'] = 'meta_value';

            if (isset($match[1]) && $match[1] == 'meta_num') {
                $this->queryVars['orderby'] = 'meta_value_num';                
            }

        } elseif (!is_null($orderBy)) {
            $this->queryVars['orderby'] = $orderBy;
        }

        if (!is_null($direction)) {
            $this->queryVars['order'] = $direction;
        }

        return $this;
    }
}
