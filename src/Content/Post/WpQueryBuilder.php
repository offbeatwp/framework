<?php
namespace OffbeatWP\Content\Post;

use InvalidArgumentException;
use OffbeatWP\Content\Traits\OffbeatQueryTrait;
use OffbeatWP\Contracts\IWpQuerySubstitute;
use OffbeatWP\Exceptions\OffbeatModelNotFoundException;
use UnexpectedValueException;
use WP_Post;
use WP_Query;

class WpQueryBuilder
{
    use OffbeatQueryTrait;

    protected $queryVars = [];
    private $wpQueryClass = WP_Query::class;

    public function all(): PostsCollection
    {
        return $this->take(-1);
    }

    /**
     * @param WP_Post $post
     * @return PostModel|null
     */
    public function postToModel($post)
    {
        return offbeat('post')->convertWpPostToModel($post);
    }

    /**
     * When true, the query will not count total rows.<br/>
     * This makes the query slighlty faster, but will not work if the total post count is required.<br/>
     * One example where total post count is required is pagination.
     * @param bool $noFoundRows
     * @return $this
     */
    public function noFoundRows(bool $noFoundRows)
    {
        $this->queryVars['no_found_rows'] = $noFoundRows;
        return $this;
    }

    public function get(): PostsCollection
    {
        if (!isset($this->queryVars['no_found_rows'])) {
            $isPaged = (bool)($this->queryVars['paged'] ?? false);
            $this->queryVars['no_found_rows'] = !$isPaged;
        }

        $query = $this->runQuery();

        return apply_filters('offbeatwp/posts/query/get', new PostsCollection($query), $this);
    }

    public function getQueryVars(): array
    {
        return $this->queryVars;
    }

    /** @return scalar|array|null */
    public function getQueryVar(string $var)
    {
        $queryVars = $this->getQueryVars();

        return $queryVars[$var] ?? null;
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

        $this->queryVars['p'] = $id;

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

    public function findBySlug(string $slug): ?PostModel
    {
        return $this->whereSlug($slug)->first();
    }

    public function findBySlugOrFail(string $slug): PostModel
    {
        $result = $this->findBySlug($slug);

        if (!$result) {
            throw new OffbeatModelNotFoundException('PostModel with slug ' . $slug . ' could not be found');
        }

        return $result;
    }

    /** @deprecated Find a post based by it's <b>slug</b>. Consider using <b>findBySlug</b> instead. */
    public function findByName(string $slug): ?PostModel
    {
        return $this->findBySlug($slug);
    }

    /** @deprecated Find a post based by it's <b>slug</b>, or throw an Exception if no post with the provided slug was found. Consider using <b>findBySlugOrFail</b> instead. */
    public function findByNameOrFail(string $slug): PostModel
    {
        return $this->findBySlugOrFail($slug);
    }

    /** @return WP_Query|IWpQuerySubstitute */
    private function runQuery()
    {
        do_action('offbeatwp/posts/query/before_get', $this);
        $query = new $this->wpQueryClass($this->queryVars);
        do_action('offbeatwp/posts/query/after_get', $this);

        self::$lastRequest = $query->request;

        return $query;
    }

    /**
     * @param positive-int $amount
     * @return $this
     */
    public function limit(int $amount)
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("Limit expects a positive number, but received {$amount}.");
        }

        $this->queryVars['posts_per_page'] = $amount;
        return $this;
    }

    /** @return int[] */
    public function ids(): array
    {
        $this->queryVars['posts_per_page'] = $this->queryVars['posts_per_page'] ?? -1;
        $this->queryVars['fields'] = 'ids';
        $this->queryVars['no_found_rows'] = true;

        return $this->runQuery()->posts;
    }

    /**
     * @param bool $forceDelete
     * @return WP_Post[] Array of all deleted post data.
     */
    public function deleteAll(bool $forceDelete): array
    {
        $deletedPosts = [];

        foreach ($this->ids() as $id) {
            $deletedPost = wp_delete_post($id, $forceDelete);

            if ($deletedPost) {
                $deletedPosts[] = $deletedPost;
            } else {
                throw new UnexpectedValueException('Failed to delete post with id: ' . $id);
            }
        }

        return $deletedPosts;
    }

    public function count(): int
    {
        $this->queryVars['posts_per_page'] = -1;
        $this->queryVars['fields'] = 'ids';
        $this->queryVars['no_found_rows'] = true;

        return $this->runQuery()->post_count;
    }

    /**
     * @param class-string<WP_Query|IWpQuerySubstitute> $queryObjectClassName
     * @return $this
     */
    public function useQuery(string $queryObjectClassName)
    {
        $this->wpQueryClass = $queryObjectClassName;

        return $this;
    }

    /**
     * @param string $metaKey
     * @param string $direction
     * @return $this
     */
    public function orderByMeta(string $metaKey, string $direction = '')
    {
        $this->queryVars['meta_key'] = $metaKey;
        $this->queryVars['orderby'] = 'meta_value';

        if ($direction) {
            $this->queryVars['order'] = $direction;
        }

        return $this;
    }

    /**
     * Note: Wordpress Pagination automatically handles offset, so using this method might interfere with that
     * @param int $numberOfItems
     * @return $this
     */
    public function offset(int $numberOfItems)
    {
        $this->queryVars['offset'] = $numberOfItems;

        return $this;
    }

    /**
     * Search keyword(s).<br>
     * Prepending a term with a hyphen will exclude posts matching that term.<br>
     * EG: 'pillow -sofa' will return posts containing 'pillow' but not 'sofa'.
     * @param string $searchString
     * @return $this
     */
    public function search(string $searchString) {
        $this->queryVars['s'] = $searchString;

        return $this;
    }

    /**
     * @param string $slug The post slug.
     * @return $this
     */
    public function whereSlug(string $slug)
    {
        $this->queryVars['name'] = $slug;

        return $this;
    }

    /**
     * @param string|string[] $postTypes
     * @return $this
     */
    public function wherePostType($postTypes)
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
     * @param string $taxonomy The taxonomy.
     * @param string|int|string[]|int[] $terms Taxonomy term(s).
     * @param string|null $field Select taxonomy term by. Possible values are ‘term_id’, ‘name’, ‘slug’ or ‘term_taxonomy_id’. Default value is ‘term_id’.
     * @param string|null $operator Operator to test. Possible values are ‘IN’, ‘NOT IN’, ‘AND’, ‘EXISTS’ and ‘NOT EXISTS’. Default value is ‘IN’.
     * @param bool $includeChildren Whether or not to include children for hierarchical taxonomies. Defaults to true.
     * @return $this
     */
    public function whereTerm(string $taxonomy, $terms = [], ?string $field = 'slug', ?string $operator = 'IN', bool $includeChildren = true)
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
    public function whereDate(array $args)
    {
        if (!isset($this->queryVars['date_query'])) {
            $this->queryVars['date_query'] = [];
        }

        $this->queryVars['date_query'][] = $args;

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
     * @return $this
     */
    public function wherePostStatus(array $postStatus)
    {
        $this->queryVars['post_status'] = $postStatus;
        return $this;
    }

    /**
     * @param string|array $key Valid keys include 'key', 'value', 'compare' and 'type'
     * @param string|int|string[]|int[] $value
     * @param string $compare
     * @return $this
     */
    public function whereMeta($key, $value = '', string $compare = '=')
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

    /**
     * @param int[]|int $ids
     * @return $this
     */
    public function whereIdNotIn($ids)
    {
        $this->queryVars['post__not_in'] = (array)$ids;
        return $this;
    }

    /**
     * @param int[]|int $ids
     * @return $this
     */
    public function whereIdIn($ids)
    {
        $this->queryVars['post__in'] = (array)$ids ?: [0];
        return $this;
    }

    /**
     * @param int[] $ids
     * @return $this
     */
    public function whereAuthorIdIn(array $ids)
    {
        $this->queryVars['author__in'] = $ids;
        return $this;
    }

    /**
     * When <i>true</i> is passed, uses the page ID from get_query_var.<br/>
     * When an <i>integer</i> is passed, the page with that number will be loaded.<br/>
     * Passing <i>0</i> or <i>false</i> will disable pagination.
     * @param bool|int $paginated
     * @return $this
     */
    public function paginated($paginated = true)
    {
        if ($paginated) {
            $this->noFoundRows(false);
            $paged = $paginated;

            if (is_bool($paginated)) {
                $paged = get_query_var('paged') ?: 1;
            }

            $this->queryVars['paged'] = $paged;
        } elseif(isset($this->queryVars['paged'])) {
            unset($this->queryVars['paged']);
        }

        return $this;
    }

    /**
     * @param bool $suppress
     * @return $this
     */
    public function suppressFilters(bool $suppress = true)
    {
        $this->queryVars['suppress_filters'] = $suppress;

        return $this;
    }

    /**
     * @param PostModel|PostsCollection $postModelOrCollection Either a PostModel or PostCollection to check a relation with.
     * @param string $key The relation key.
     * @param null $direction Pass <b>'reverse'</b> to reverse the relation.
     * @return $this
     */
    public function hasRelationshipWith($postModelOrCollection, $key, $direction = null)
    {
        $this->queryVars['relationships'] = [
            'id' => ($postModelOrCollection instanceof PostsCollection) ? $postModelOrCollection->getIds() : $postModelOrCollection->getId(),
            'key' => $key,
            'direction' => $direction,
        ];

        return $this;
    }
}
