<?php
namespace OffbeatWP\Content\Post;

use OffbeatWP\Exceptions\OffbeatModelNotFoundException;
use WP_Post;
use WP_Query;

final class PostQueryBuilder
{
    protected string $modelClass;
    protected array $queryArgs = ['nopaging' => true, 'no_found_rows' => true];

    /** @param class-string<PostModel> $modelClass */
    public function __construct(string $modelClass) {
        $this->modelClass = $modelClass;

        if ($modelClass && $modelClass::postType()) {
            $this->wherePostType($modelClass::postType());
        }
    }

    /** @deprecated */
    public function all(): PostsCollection
    {
        return $this->get();
    }

    public function each(callable $callback): PostsCollection
    {
        return $this->get()->each($callback);
    }

    public function postToModel(WP_Post $post): PostModel
    {
        return offbeat('post')->convertWpPostToModel($post);
    }

    /** @return PostsCollection<PostModel> */
    public function get(): PostsCollection
    {
        do_action('offbeatwp/posts/query/before_get', $this);

        $posts = new WP_Query($this->queryArgs);

        return apply_filters('offbeatwp/posts/query/get', new PostsCollection($posts), $this);
    }

    public function getQueryArgs(): array
    {
        return $this->queryArgs;
    }

    public function getQueryArg(string $var): array|bool|string|float|int|null
    {
        return $this->queryArgs[$var] ?? null;
    }

    public function take(int $numberOfItems): PostsCollection
    {
        $this->queryArgs['posts_per_page'] = $numberOfItems;

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

        if (!$result) {
            throw new OffbeatModelNotFoundException('The query did not return any Postmodels');
        }

        return $result;
    }

    public function findById(?int $id): ?PostModel
    {
        if ($id <= 0) {
            return null;
        }

        $this->queryArgs['p'] = $id;

        return $this->first();
    }

    /** @throws OffbeatModelNotFoundException */
    public function findByIdOrFail(int $id): PostModel
    {
        $result = $this->findById($id);

        if (!$result) {
            throw new OffbeatModelNotFoundException('PostModel with id ' . $id . ' could not be found');
        }

        return $result;
    }

    public function findByName(string $name): ?PostModel
    {
        $this->queryArgs['name'] = $name;

        return $this->first();
    }

    /** @throws OffbeatModelNotFoundException */
    public function findByNameOrFail(string $name): PostModel
    {
        $result = $this->findByName($name);

        if (!$result) {
            throw new OffbeatModelNotFoundException('PostModel with name ' . $name . ' could not be found');
        }

        return $result;
    }

    public function orderByMeta(string $metaKey, string $direction = ''): static
    {
        $this->queryArgs['meta_key'] = $metaKey;
        $this->queryArgs['orderby'] = 'meta_value';

        if ($direction) {
            $this->queryArgs['order'] = $direction;
        }

        return $this;
    }

    /** Wordpress Pagination automatically handles offset, so using this method might interfere with that */
    public function offset(int $numberOfItems): static
    {
        $this->queryArgs['offset'] = $numberOfItems;
        return $this;
    }

    /** @param string[] $postTypes */
    public function wherePostType(array $postTypes): static
    {
        $this->queryArgs['post_type'] = $postTypes;
        return $this;
    }

    /**
     * @param string $taxonomy The taxonomy.
     * @param string|int|string[]|int[] $terms Taxonomy term(s).
     * @param string|null $field Select taxonomy term by. Possible values are ‘term_id’, ‘name’, ‘slug’ or ‘term_taxonomy_id’. Default value is ‘term_id’.
     * @param string|null $operator Operator to test. Possible values are ‘IN’, ‘NOT IN’, ‘AND’, ‘EXISTS’ and ‘NOT EXISTS’. Default value is ‘IN’.
     * @param bool $includeChildren Whether or not to include children for hierarchical taxonomies. Defaults to true.
     */
    public function whereTerm(string $taxonomy, $terms = [], ?string $field = 'slug', ?string $operator = 'IN', bool $includeChildren = true): static
    {
        if ($field === null) {
            $field = 'slug';
        }

        if (!is_array($terms)) {
            $terms = [$terms];
        }

        if ($operator === null) {
            $operator = 'IN';
        }

        if (!isset($this->queryArgs['tax_query'])) {
            $this->queryArgs['tax_query'] = [];
        }

        $parameters = [
            'taxonomy' => $taxonomy,
            'field' => $field,
            'terms' => $terms,
            'operator' => $operator,
            'include_children' => $includeChildren,
        ];

        $this->queryArgs['tax_query'][] = $parameters;

        return $this;
    }

    /**
     * @param int[]|string[] $args
     * @return $this
     */
    public function whereDate(array $args): static
    {
        if (!isset($this->queryArgs['date_query'])) {
            $this->queryArgs['date_query'] = [];
        }

        $this->queryArgs['date_query'][] = $args;

        return $this;
    }

    /**
     * Retrieves posts by post status. Default value is <i>publish</i>, but if the user is logged in, <i>private</i> is added. Public custom post statuses are also included by default.<br/>
     * If the query is run in an admin/ajax context, protected statuses are added too.<br/>
     * By default protected statuses are <i>future</i>, <i>draft</i> and <i>pending</i>.<br/><br/>
     *
     * The default WP post statuses are:<br/>
     * <b>publish</b> – a published post or page<br/>
     * <b>pending</b> – post is pending review<br/>
     * <b>draft</b> – a post in draft status<br/>
     * <b>auto-draft</b> – a newly created post, with no content<br/>
     * <b>future</b> – a post to publish in the future<br/> <b>private</b> – not visible to users who are not logged in<br/>
     * <b>inherit</b> – a revision, see get_children()
     * <b>trash</b> – post is in trashbin<br/>
     * <b>any</b> – retrieves any status except for <i>inherit</i>, <i>trash</i> and <i>auto-draft</i>. Custom post statuses with <i>exclude_from_search</i> set to true are also excluded
     * @param string[] $postStatus Array containing the post statuses to include
     * @return static
     */
    public function wherePostStatus(array $postStatus): static
    {
        $this->queryArgs['post_status'] = $postStatus;
        return $this;
    }

    /**
     * @param string|array $key Valid keys include 'key', 'value', 'compare' and 'type'
     * @param string|int|string[]|int[] $value
     * @param string $compare
     * @return $this
     */
    public function whereMeta($key, $value = '', string $compare = '='): static
    {
        if (!isset($this->queryArgs['meta_query'])) {
            $this->queryArgs['meta_query'] = [];
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

        $this->queryArgs['meta_query'][] = $parameters;

        return $this;
    }

    /** @param int[]|int $ids */
    public function whereIdNotIn($ids): static
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $this->queryArgs['post__not_in'] = $ids;

        return $this;
    }

    /** @param int[]|int $ids */
    public function whereIdIn($ids): static
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $this->queryArgs['post__in'] = $ids;

        return $this;
    }

    public function paginated(bool $paginated = true): static
    {
        if ($paginated) {
            $paged = $paginated;

            if (is_bool($paginated)) {
                $paged = get_query_var('paged') ?: 1;
            }

            $this->queryArgs['paged'] = $paged;
        } else {
            unset($this->queryArgs['paged']);
        }

        return $this;
    }

    public function suppressFilters(bool $suppress = true): static
    {
        $this->queryArgs['suppress_filters'] = $suppress;

        return $this;
    }

    /**
     * @param PostModel $model
     * @param string $key
     * @param string|null $direction
     * @return $this
     */
    public function hasRelationshipWith($model, $key, ?string $direction = null): static
    {
        $this->queryArgs['relationships'] = [
            'id' => $model->getId(),
            'key' => $key,
            'direction' => $direction,
        ];

        return $this;
    }
}
