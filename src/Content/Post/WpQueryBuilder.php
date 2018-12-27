<?php
namespace OffbeatWP\Content\Post;

use OffbeatWP\Content\Post\PostsCollection;

class WpQueryBuilder
{

    protected $model;
    protected $queryVars = [];

    public function __construct($model)
    {
        $this->model = $model;

        $this->queryVars = [
            'post_type' => $model::POST_TYPE,
        ];

        if (defined("{$model}::ORDER")) {
            $this->queryVars['order'] = $model::ORDER;
        }

        if (defined("{$model}::ORDER_BY")) {
            $this->queryVars['order_by'] = $model::ORDER_BY;
        }
    }

    public function all()
    {
        $this->queryVars['posts_per_page'] = -1;
        
        return $this->get();
    }

    public function get()
    {
        $postModels = [];
        $posts = new \WP_Query($this->queryVars);

        if (!empty($posts->posts)) foreach ($posts->posts as $post) {
            array_push($postModels, new $this->model($post));
        }

        return new PostsCollection($postModels);
    }

    public function take($numberOfItems)
    {
        $this->queryVars['posts_per_page'] = $numberOfItems;

        return $this->get();
    }

    public function first()
    {
        $this->queryVars['posts_per_page'] = 1;

        return $this->get()->first();
    }

    public function findById($id)
    {
        $this->queryVars['p'] = $id;

        return $this->first();
    }

    public function where($parameters)
    {
        $this->queryVars = array_merge($this->queryVars, $parameters);

        return $this;
    }

    public function whereTerm($taxonomy, $terms = [], $field = 'slug', $operator = 'IN')
    {
        if (is_null($field)) {
            $field = 'slug';
        }

        if (is_null($operator)) {
            $operator = 'IN';
        }

        if (!isset($this->queryVars['tax_query'])) {
            $this->queryVars['tax_query'] = [];
        }

        if (is_array($terms)) {
            $parameters = [
                'taxonomy' => $taxonomy,
                'field'    => $field,
                'terms'    => $terms,
                'operator' => $operator,
            ];
        }

        array_push($this->queryVars['tax_query'], $parameters);

        return $this;
    }

    public function whereDate($parameters)
    {
        if (!isset($this->queryVars['date_query'])) {
            $this->queryVars['date_query'] = [];
        }

        array_push($this->queryVars['date_query'], $parameters);

        return $this;
    }

    public function whereMeta($key, $value = '', $compare = '=')
    {
        if (!isset($this->queryVars['meta_query'])) {
            $this->queryVars['meta_query'] = [];
        }

        if (!is_array($key)) {
            $parameters = [
                'key'     => $key,
                'value'   => $value,
                'compare' => $compare,
            ];
        }

        array_push($this->queryVars['meta_query'], $parameters);

        return $this;
    }

    public function order($order = null, $direction = null) {
        if (!is_null($order)) {
            $this->queryVars['order'] = $order;
        }

        if (!is_null($direction)) {
            $this->queryVars['order_by'] = $direction;
        }
    }
}
