<?php

namespace OffbeatWP\Content\Post;

use OffbeatWP\Content\AbstractQueryBuilder;
use WP_Query;

class WpQueryBuilder extends AbstractQueryBuilder
{
    protected $queryVars = [];

    public function all(): PostsCollection
    {
        $this->queryVars['posts_per_page'] = -1;

        return $this->get();
    }

    public function postToModel($post)
    {
        return offbeat('post')->convertWpPostToModel($post);
    }

    public function get(): PostsCollection
    {
        $posts = new WP_Query($this->queryVars);

        return new PostsCollection($posts);
    }

    public function getQueryVars(): array
    {
        return $this->queryVars;
    }

    public function take(int $numberOfItems): PostsCollection
    {
        $this->queryVars['posts_per_page'] = $numberOfItems;

        return $this->get();
    }

    public function first(): ?PostModel
    {
        $this->queryVars['posts_per_page'] = 1;

        return $this->get()->first();
    }

    public function findById(int $id): ?PostModel
    {
        $this->queryVars['p'] = $id;
        $this->queryVars['post_type'] = 'any';

        return $this->first();
    }

    public function findByName(string $name): ?PostModel
    {
        $this->queryVars['name'] = $name;

        return $this->first();
    }

    /** @param string|string[] $postTypes */
    public function wherePostType($postTypes): WpQueryBuilder
    {
        if (!isset($this->queryVars['post_type'])) {
            $this->queryVars['post_type'] = [];
        }

        if (is_string($postTypes)) {
            $postTypes = [$postTypes];
        }

        $this->queryVars['post_type'] = array_merge($this->queryVars['post_type'], $postTypes);

        return $this;
    }

    /**
     * @param string $taxonomy
     * @param string|int|string[]|int[] $terms
     * @param string|null $field
     * @param string|null $operator
     * @param bool $includeChildren
     * @return WpQueryBuilder
     */
    public function whereTerm(string $taxonomy, $terms = [], ?string $field = 'slug', ?string $operator = 'IN', bool $includeChildren = true): WpQueryBuilder
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

        $parameters = [
            'taxonomy' => $taxonomy,
            'field' => $field,
            'terms' => $terms,
            'operator' => $operator,
            'include_children' => $includeChildren,
        ];

        array_push($this->queryVars['tax_query'], $parameters);

        return $this;
    }

    /**
     * @param int[]|string[]|bool[]|string[][] $dateParams
     * @return WpQueryBuilder
     */
    public function whereDate(array $dateParams): WpQueryBuilder
    {
        if (!isset($this->queryVars['date_query'])) {
            $this->queryVars['date_query'] = [];
        }

        array_push($this->queryVars['date_query'], $dateParams);

        return $this;
    }

    /**
     * @param string|array $key
     * @param string|array $value
     * @param string $compare
     * @return WpQueryBuilder
     */
    public function whereMeta($key, $value = '', string $compare = '='): WpQueryBuilder
    {
        if (!isset($this->queryVars['meta_query'])) {
            $this->queryVars['meta_query'] = [];
        }

        if (is_array($key)) {
            $parameters = $key;
        } else {
            $parameters = [
                'key' => $key,
                'value' => $value,
                'compare' => $compare,
            ];
        }

        array_push($this->queryVars['meta_query'], $parameters);

        return $this;
    }

    /** @param int[]|int $ids */
    public function whereIdNotIn($ids): WpQueryBuilder
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $this->queryVars['post__not_in'] = $ids;

        return $this;
    }

    /** @param int[]|int $ids */
    public function whereIdIn($ids): WpQueryBuilder
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $this->queryVars['post__in'] = $ids;

        return $this;
    }

    public function paginated(bool $paginated = true): WpQueryBuilder
    {
        if ($paginated) {
            $paged = $paginated;

            if (is_bool($paginated)) {
                $paged = get_query_var('paged') ? get_query_var('paged') : 1;
            }

            $this->queryVars['paged'] = $paged;
        } else {
            unset($this->queryVars['paged']);
        }

        return $this;
    }

    public function hasRelationshipWith($model, $key, $direction = null): WpQueryBuilder
    {
        $this->queryVars['relationships'] = [
            'id' => $model->getId(),
            'key' => $key,
            'direction' => $direction,
        ];

        return $this;
    }
}
