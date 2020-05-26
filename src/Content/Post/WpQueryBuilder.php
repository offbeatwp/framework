<?php
namespace OffbeatWP\Content\Post;

class WpQueryBuilder
{
    protected $queryVars = [];

    public function all()
    {
        $this->queryVars['posts_per_page'] = -1;
        
        return $this->get();
    }

    public function postToModel($post)
    {
        return offbeat('post')->convertWpPostToModel($post);
    }

    public function get()
    {
        $posts = new \WP_Query($this->queryVars);

        return new PostsCollection($posts);
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
        $this->queryVars['post_type'] = 'any';

        return $this->first();
    }
    
    public function findByName($name)
    {
        $this->queryVars['name'] = $name;

        return $this->first();
    }

    public function where($args)
    {
        $this->queryVars = array_merge($this->queryVars, $args);

        return $this;
    }

    public function wherePostType($postTypes)
    {
        if (!isset($this->queryVars['post_type'])) {
            $this->queryVars['post_type'] = [];
        }

        if (is_string($postTypes)) $postTypes = [$postTypes];

        $this->queryVars['post_type'] = array_merge($this->queryVars['post_type'], $postTypes);

        return $this;
    }

    public function whereTerm($taxonomy, $terms = [], $field = 'slug', $operator = 'IN', $includeChildren = true)
    {
        if (is_null($field)) {
            $field = 'slug';
        }

        if (!is_array($terms)) {
            $terms = [$terms];
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
                'include_children' => $includeChildren,
            ];
        }

        array_push($this->queryVars['tax_query'], $parameters);

        return $this;
    }

    public function whereDate($args)
    {
        if (!isset($this->queryVars['date_query'])) {
            $this->queryVars['date_query'] = [];
        }

        array_push($this->queryVars['date_query'], $args);

        return $this;
    }

    public function whereMeta($key, $value = '', $compare = '=')
    {
        if (!isset($this->queryVars['meta_query'])) {
            $this->queryVars['meta_query'] = [];
        }

        if (is_array($key)) {
            $parameters = $key;
        } else {
            $parameters = [
                'key'     => $key,
                'value'   => $value,
                'compare' => $compare,
            ];
        }

        array_push($this->queryVars['meta_query'], $parameters);

        return $this;
    }

    public function whereIdNotIn($ids) {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $this->queryVars['post__not_in'] = $ids;

        return $this;
    }

    public function whereIdIn($ids) {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $this->queryVars['post__in'] = $ids;

        return $this;
    }

    public function order($orderBy = null, $direction = null) {
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
    
    public function paginated() {
        $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
        $this->queryVars['paged'] = $paged;
        return $this;
    }

    public function hasRelationshipWith($model, $key, $direction = null) {
        $this->queryVars['relationships'] = [
            'id' => $model->getId(),
            'key' => $key,
            'direction' => $direction,
        ];

        return $this;
    }
}
