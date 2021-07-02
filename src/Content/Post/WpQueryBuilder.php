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

        return $this->execute();
    }

    public function postToModel($post)
    {
        return offbeat('post')->convertWpPostToModel($post);
    }

    private function execute(): PostsCollection
    {
        $posts = new WP_Query($this->queryVars);

        return new PostsCollection($posts);
    }

    /** @deprecated You probably meant to use all() or take(10) */
    public function get(): PostsCollection
    {
        return $this->execute();
    }

    public function getQueryVars(): array
    {
        return $this->queryVars;
    }

    public function take($numberOfItems): PostsCollection
    {
        $this->queryVars['posts_per_page'] = $numberOfItems;

        return $this->execute();
    }

    public function first(): ?PostModel
    {
        $this->queryVars['posts_per_page'] = 1;

        return $this->execute()->first();
    }

    public function findById($id): ?PostModel
    {
        $this->queryVars['p'] = $id;
        $this->queryVars['post_type'] = 'any';

        return $this->first();
    }

    public function findByName($name): ?PostModel
    {
        $this->queryVars['name'] = $name;

        return $this->first();
    }

    public function wherePostType($postTypes): WpQueryBuilder
    {
        if (!isset($this->queryVars['post_type'])) {
            $this->queryVars['post_type'] = [];
        }

        if (is_string($postTypes)) $postTypes = [$postTypes];

        $this->queryVars['post_type'] = array_merge($this->queryVars['post_type'], $postTypes);

        return $this;
    }

    public function whereTerm($taxonomy, $terms = [], $field = 'slug', $operator = 'IN', $includeChildren = true): WpQueryBuilder
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

        $parameters = null;
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

    /** @var bool[]|int[]|string[]|string[][] $dateArgs */
    public function whereDate(array $dateArgs): WpQueryBuilder
    {
        if (!isset($this->queryVars['date_query'])) {
            $this->queryVars['date_query'] = [];
        }

        array_push($this->queryVars['date_query'], $dateArgs);

        return $this;
    }

    public function whereMeta($key, $value = '', $compare = '='): WpQueryBuilder
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

    public function paginated($paginated = true): WpQueryBuilder
    {
        if ($paginated) {
            $paged = $paginated;

            if (is_bool($paginated)) {
                $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
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
