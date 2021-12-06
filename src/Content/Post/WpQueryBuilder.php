<?php
namespace OffbeatWP\Content\Post;

use OffbeatWP\Content\AbstractQueryBuilder;
use OffbeatWP\Exceptions\OffbeatModelNotFoundException;
use WP_Query;

class WpQueryBuilder extends AbstractQueryBuilder
{
    protected $queryVars = [];

    public function all(): PostsCollection
    {
        return $this->take(-1);
    }

    public function postToModel($post)
    {
        return offbeat('post')->convertWpPostToModel($post);
    }

    /** @return PostsCollection<PostModel> */
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
        return $this->take(1)->first();
    }

    /** @throws OffbeatModelNotFoundException */
    public function firstOrFail(): PostModel
    {
        $result = $this->first();

        if (empty($result)) {
            throw new OffbeatModelNotFoundException('The query did not return any Postmodels');
        }

        return $result;
    }

    public function findById(int $id): ?PostModel
    {
        $this->queryVars['p'] = $id;

        return $this->first();
    }

    /** @throws OffbeatModelNotFoundException */
    public function findByIdOrFail(int $id): PostModel
    {
        $result = $this->findById($id);

        if (empty($result)) {
            throw new OffbeatModelNotFoundException('PostModel with id ' . $id . ' could not be found');
        }

        return $result;
    }

    public function findByName(string $name): ?PostModel
    {
        $this->queryVars['name'] = $name;

        return $this->first();
    }

    /** @throws OffbeatModelNotFoundException */
    public function findByNameOrFail(string $name): PostModel
    {
        $result = $this->findByName($name);

        if (empty($result)) {
            throw new OffbeatModelNotFoundException('PostModel with name ' . $name . ' could not be found');
        }

        return $result;
    }

    public function orderByMeta(string $metaKey): AbstractQueryBuilder
    {
        $this->queryVars['meta_key'] = $metaKey;
        $this->queryVars['orderby'] = 'meta_value';

        return $this;
    }

    /** Wordpress Pagination automatically handles offset, so using this method might interfere with that */
    public function offset(int $numberOfItems): WpQueryBuilder
    {
        $this->queryVars['offset'] = $numberOfItems;

        return $this;
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

        $this->queryVars['tax_query'][] = $parameters;

        return $this;
    }

    /**
     * @param int[]|string[] $args
     * @return $this
     */
    public function whereDate(array $args): WpQueryBuilder
    {
        if (!isset($this->queryVars['date_query'])) {
            $this->queryVars['date_query'] = [];
        }

        $this->queryVars['date_query'][] = $args;

        return $this;
    }

    /**
     * @param string|array $key Valid keys include 'key', 'value', 'compare' and 'type'
     * @param string|int|string[]|int[] $value
     * @param string $compare
     * @return $this
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
                'key'     => $key,
                'value'   => $value,
                'compare' => $compare,
            ];
        }

        $this->queryVars['meta_query'][] = $parameters;

        return $this;
    }

    /** Select items where the specified meta key is 0 OR non-existent */
    public function whereMetaIsFalsy(string $metaKey): WpQueryBuilder
    {
        $this->whereMeta([
            'relation' => 'OR',
            ['key' => $metaKey, 'compare' => '=', 'value' => '0'],
            ['key' => $metaKey, 'compare' => 'NOT EXISTS']
        ]);

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
                $paged = get_query_var('paged') ?: 1;
            }

            $this->queryVars['paged'] = $paged;
        } else {
            unset($this->queryVars['paged']);
        }

        return $this;
    }

    public function suppressFilters(bool $suppress = true): WpQueryBuilder
    {
        $this->queryVars['suppress_filters'] = $suppress;

        return $this;
    }

    public function hasRelationshipWith(PostModel $model, string $key, ?string $direction = null): WpQueryBuilder
    {
        $this->queryVars['relationships'] = [
            'id' => $model->getId(),
            'key' => $key,
            'direction' => $direction,
        ];

        return $this;
    }
}
