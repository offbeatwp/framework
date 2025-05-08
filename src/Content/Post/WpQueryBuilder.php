<?php

namespace OffbeatWP\Content\Post;

use Generator;
use InvalidArgumentException;
use OffbeatWP\Content\Traits\OffbeatQueryTrait;
use OffbeatWP\Contracts\IWpQuerySubstitute;
use OffbeatWP\Exceptions\OffbeatModelNotFoundException;
use UnexpectedValueException;
use WP_Post;
use WP_Query;

/** @template TValue of PostModel */
class WpQueryBuilder
{
    use OffbeatQueryTrait;

    /** @var array<string, mixed> */
    protected array $queryVars = ['post_type' => 'any'];
    private string $wpQueryClass = WP_Query::class;

    /** @return PostsCollection<int, TValue> */
    final public function all(): PostsCollection
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
     * @return $this
     */
    final public function noFoundRows(bool $noFoundRows)
    {
        $this->queryVars['no_found_rows'] = $noFoundRows;
        return $this;
    }

    /**
     * Shows all posts rather than paginating.
     * @return $this
     */
    final public function noPaging()
    {
        $this->queryVars['nopaging'] = true;
        unset($this->queryVars['paged']);

        return $this;
    }

    /** @return PostsCollection<int, TValue> */
    final public function get(): PostsCollection
    {
        if (!isset($this->queryVars['no_found_rows'])) {
            $isPaged = (bool)($this->queryVars['paged'] ?? false);
            $this->queryVars['no_found_rows'] = !$isPaged;
        }

        $query = $this->runQuery();

        return apply_filters('offbeatwp/posts/query/get', new PostsCollection($query), $this);
    }

    /**
     * @return Generator<\OffbeatWP\Content\Post\PostModel>
     * @phpstan-return Generator<TValue>
     */
    final public function getGenerator(): Generator
    {
        if (!isset($this->queryVars['no_found_rows'])) {
            $isPaged = (bool)($this->queryVars['paged'] ?? false);
            $this->queryVars['no_found_rows'] = !$isPaged;
        }

        foreach ($this->runQuery()->get_posts() as $post) {
            yield offbeat('post')->convertWpPostToModel($post);
        }
    }

    /** @return mixed[] */
    final public function getQueryVars(): array
    {
        return $this->queryVars;
    }

    /** @return scalar|mixed[]|null */
    final public function getQueryVar(string $var)
    {
        $queryVars = $this->getQueryVars();

        return $queryVars[$var] ?? null;
    }

    /** @return PostsCollection<int, TValue> */
    final public function take(int $numberOfItems): PostsCollection
    {
        $this->queryVars['posts_per_page'] = $numberOfItems;

        return $this->get();
    }

    public function first(): ?PostModel
    {
        return $this->take(1)->first();
    }

    public function firstOrFail(): PostModel
    {
        $result = $this->first();

        if (!$result) {
            throw new OffbeatModelNotFoundException('The query did not return any Postmodels');
        }

        return $result;
    }

    /** @phpstan-return TValue|null */
    final public function findById(?int $id): ?PostModel
    {
        if ($id <= 0) {
            return null;
        }

        $this->queryVars['p'] = $id;

        return $this->first();
    }

    /** @phpstan-return TValue */
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
    final public function findByName(string $slug): ?PostModel
    {
        return $this->findBySlug($slug);
    }

    /** @deprecated Find a post based by it's <b>slug</b>, or throw an Exception if no post with the provided slug was found. Consider using <b>findBySlugOrFail</b> instead. */
    final public function findByNameOrFail(string $slug): PostModel
    {
        return $this->findBySlugOrFail($slug);
    }

    private function runQuery(): WP_Query|IWpQuerySubstitute
    {
        do_action('offbeatwp/posts/query/before_get', $this);
        $query = new $this->wpQueryClass($this->queryVars);
        do_action('offbeatwp/posts/query/after_get', $this);

        self::$lastRequest = $query->request;

        return $query;
    }

    /** @return $this */
    final public function limit(int $amount)
    {
        if ($amount < 0 && $amount !== -1) {
            throw new InvalidArgumentException("Limit expects a positive number, but received {$amount}.");
        }

        $this->queryVars['posts_per_page'] = $amount;

        return $this;
    }

    /** @return positive-int[] */
    final public function ids(): array
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
    final public function deleteAll(bool $forceDelete): array
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

    final public function count(): int
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
    final public function useQuery(string $queryObjectClassName)
    {
        $this->wpQueryClass = $queryObjectClassName;
        return $this;
    }

    /**
     * Note: Wordpress Pagination automatically handles offset, so using this method might interfere with that
     * @param int $numberOfItems
     * @return $this
     */
    final public function offset(int $numberOfItems)
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
    final public function search(string $searchString)
    {
        $this->queryVars['s'] = $searchString;
        return $this;
    }

    /**
     * @param string $slug The post slug.
     * @return $this
     */
    final public function whereSlug(string $slug)
    {
        $this->queryVars['name'] = $slug;
        return $this;
    }

    /**
     * @param string|string[] $postTypes
     * @return $this
     */
    final public function wherePostType(string|array $postTypes)
    {
        $this->queryVars['post_type'] = is_string($postTypes) ? [$postTypes] : $postTypes;

        return $this;
    }

    /**
     * @param string $taxonomy The taxonomy.
     * @param string|int|string[]|int[] $terms Taxonomy term(s).
     * @param "term_id"|"name"|"slug"|"term_taxonomy_id"|null $field Select taxonomy term by. Possible values are 'term_id', 'name', 'slug' or 'term_taxonomy_id'. Default value is 'slug'.
     * @param "IN"|"NOT IN"|"AND"|"EXISTS"|"NOT EXISTS"|null $operator Operator to test. Possible values are 'IN', 'NOT IN', 'AND', 'EXISTS' and 'NOT EXISTS'. Default value is 'IN'.
     * @param bool $includeChildren Whether or not to include children for hierarchical taxonomies. Defaults to true.
     * @return $this
     */
    final public function whereTerm(string $taxonomy, string|int|array $terms = [], ?string $field = 'slug', ?string $operator = 'IN', bool $includeChildren = true)
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
     * @phpstan-param array{year?: int, month?: int, week?: int, day?: int, hour?: int, minute?: int, second?: int, after?: string[], before?: string[], inclusive?: bool, compare?: string, column: ?string, relation?: string}|array{year?: int, month?: int, week?: int, day?: int, hour?: int, minute?: int, second?: int, after?: string[], before?: string[], inclusive?: bool, compare?: string, column: ?string, relation?: string}[] $args
     * @param string[]|int[]|string[][]|int[][] $args
     * @return $this
     */
    final public function whereDate(array $args)
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
    final public function wherePostStatus(array $postStatus)
    {
        $this->queryVars['post_status'] = $postStatus;
        return $this;
    }

    /**
     * @param non-empty-string|mixed[] $key
     * @param scalar|scalar[] $value
     * @param "="|"!="|">"|">="|"<"|"<="|"LIKE"|"NOT LIKE"|"IN"|"NOT IN"|"BETWEEN"|"NOT BETWEEN"|"EXISTS"|"NOT EXISTS"|"REGEXP"|"NOT REGEXP"|"RLIKE" $compare
     * @param "NUMERIC"|"BINARY"|"CHAR"|"DATE"|"DATETIME"|"DECIMAL"|"SIGNED"|"TIME"|"UNSIGNED" $type
     * @return $this
     */
    final public function whereMeta($key, $value = '', string $compare = '=', string $type = 'CHAR')
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
                'type'    => $type
            ];
        }

        $this->queryVars['meta_query'][] = $parameters;

        return $this;
    }

    /**
     * @param int[]|int $ids
     * @return $this
     */
    final public function whereIdNotIn($ids)
    {
        $this->queryVars['post__not_in'] = (array)$ids;
        return $this;
    }

    /**
     * @param int[]|int $ids
     * @return $this
     */
    final public function whereIdIn(array|int $ids)
    {
        $this->queryVars['post__in'] = (array)$ids ?: [0];
        return $this;
    }

    /**
     * @param int[] $ids
     * @return $this
     */
    final public function whereAuthorIdIn(array $ids)
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
    final public function paginated(int|bool $paginated = true)
    {
        if ($paginated) {
            $this->noFoundRows(false);
            $paged = $paginated;

            if (is_bool($paginated)) {
                $paged = get_query_var('paged') ?: 1;
            }

            $this->queryVars['paged'] = $paged;
        } elseif (isset($this->queryVars['paged'])) {
            unset($this->queryVars['paged']);
        }

        return $this;
    }

    /** @return $this */
    final public function suppressFilters(bool $suppress = true)
    {
        $this->queryVars['suppress_filters'] = $suppress;
        return $this;
    }

    /**
     * @param PostModel|PostsCollection<int|string, PostModel>|int $postModelOrCollection Either a PostModel or PostCollection to check a relation with.
     * @param string $relationKey The relation key.
     * @param string|null $direction Pass <b>'reverse'</b> to reverse the relation.
     * @return $this
     */
    final public function hasRelationshipWith(PostModel|PostsCollection|int $postModelOrCollection, string $relationKey, ?string $direction = null)
    {
        if ($postModelOrCollection instanceof PostsCollection) {
            $id = $postModelOrCollection->getIds();
        } elseif ($postModelOrCollection instanceof PostModel) {
            $id = $postModelOrCollection->getId();
        } else {
            $id = $postModelOrCollection;
        }

        $this->queryVars['relationships'] = [
            'id' => $id,
            'key' => $relationKey,
            'direction' => $direction,
        ];

        return $this;
    }

    /**
     * @param int[] $ids Array of ID's to check a relation
     * @param string $relationKey The relation key
     * @param bool $inverted Set to <i>true</i> to reverse the relation
     * @return $this
     */
    final public function whereRelatedTo(array $ids, string $relationKey, bool $inverted = false)
    {
        $this->queryVars['relationships'] = [
            'id' => $ids,
            'key' => $relationKey,
            'direction' => ($inverted) ? 'reverse' : null,
        ];

        return $this;
    }

    /**
     * Display password-protected posts?<br>
     * <i>true</i> for posts with passwords<br>
     * <i>false</i> for posts without passwords<br>
     * <i>null</i> for all posts with and without passwords
     * @return $this
     */
    final public function hasPassword(?bool $hasPassword)
    {
        $this->queryVars['has_password'] = $hasPassword;
        return $this;
    }

    /**
     * Display posts with a particular password.
     * @return $this
     */
    final public function postPassword(string $password)
    {
        $this->queryVars['post_password'] = $password;
        return $this;
    }

    /**
     * Sort retrieved posts by parameter. Defaults to 'date (post_date)’. One or more options can be passed.<br>
     * 'none' – No order.<br>
     * 'ID' – Order by post id. Note the capitalization.<br>
     * 'author' – Order by author.<br>
     * 'title' – Order by title.<br>
     * 'name' – Order by post name (post slug).<br>
     * 'type' – Order by post type (available since version 4.0).
     * 'date' – Order by date.<br>
     * 'modified' – Order by last modified date.<br>
     * 'parent' – Order by post/page parent id.<br>
     * 'rand' – Random order.<br>
     * 'comment_count' – Order by number of comments.<br>
     * 'relevance' – Order by search terms in the following order: First, whether the entire sentence is matched. Second, if all the search terms are within the titles. Third, if any of the search terms appear in the titles. And, fourth, if the full sentence appears in the contents.<br>
     * 'menu_order' – Order by Page Order. Used most often for pages (Order field in the Edit Page Attributes box) and for attachments (the integer fields in the Insert / Upload Media Gallery dialog), but could be used for any post type with distinct 'menu_order' values (they all default to 0).<br>
     * 'meta_value' – Note that a 'meta_key=keyname' must also be present in the query. Note also that the sorting will be alphabetical which is fine for strings (i.e. words), but can be unexpected for numbers (e.g. 1, 3, 34, 4, 56, 6, etc, rather than 1, 3, 4, 6, 34, 56 as you might naturally expect). Use 'meta_value_num' instead for numeric values. You may also specify 'meta_type' if you want to cast the meta value as a specific type. Possible values are 'NUMERIC’, 'BINARY’, 'CHAR’, 'DATE’, 'DATETIME’, 'DECIMAL’, 'SIGNED’, 'TIME’, 'UNSIGNED’, same as in '$meta_query'.<br>
     * 'meta_value_num' – Order by numeric meta value. Also note that a 'meta_key=keyname' must also be present in the query. This value allows for numerical sorting as noted above in 'meta_value'.<br>
     * 'post__in' – Preserve post ID order given in the post__in array (available since version 3.5). Note – the value of the order parameter does not change the resulting sort order.<br>
     * 'post_name__in' – Preserve post slug order given in the 'post_name__in’ array (available since Version 4.6). Note – the value of the order parameter does not change the resulting sort order.<br>
     * 'post_parent__in' -Preserve post parent order given in the 'post_parent__in’ array (available since Version 4.6). Note – the value of the order parameter does not change the resulting sort order.<br>
     *
     * @param 'none'|'ID'|'author'|'title'|'name'|'type'|'date'|'modified'|'parent'|'rand'|'comment_count'|'relevance'|'menu_order'|'meta_value'|'meta_value_num'|'post__in'|'post_name__in'|'post_parent__in'|('none'|'ID'|'author'|'title'|'name'|'type'|'date'|'modified'|'parent'|'rand'|'comment_count'|'relevance'|'menu_order'|'meta_value'|'meta_value_num'|'post__in'|'post_name__in'|'post_parent__in')[] $orderBy
     * @return $this
     */
    final public function orderBy(string|array $orderBy)
    {
        $this->queryVars['orderby'] = $orderBy;
        return $this;
    }

    /**
     * Whether to cache post information. Default true.
     * @return $this
     */
    final public function cacheResults(bool $cacheResults)
    {
        $this->queryVars['cache_results'] = $cacheResults;
        return $this;
    }

    /**
     * Select only specific post fields. Returns an array of WP_Posts.<br>
     * <b>Note:</b> Values not included in the query will have their default WP_Post value.
     * @see WP_Post
     * @param non-empty-array<'ID'|'post_author'|'post_date'|'post_date_gmt'|'post_content'|'post_title'|'post_excerpt'|'post_status'|'comment_status'|'ping_status'|'post_password'|'post_name'|'to_ping'|'pinged'|'post_modified'|'post_modified_gmt'|'post_content_filtered'|'post_parent'|'guid'|'menu_order'|'post_type'|'post_mime_type'|'comment_count'> $columns
     * @return WP_Post[]
     */
    final public function only(array $columns): array
    {
        if (!$columns) {
            throw new InvalidArgumentException('WpQueryBuilder::only method cannot receive an empty array.');
        }

        $this->queryVars['owp-fields'] = $columns;

        return $this->runQuery()->get_posts();
    }

    /**
     * Pluck a specific post field from the database.<br>
     * Optionally, you can provide a second column whose value will be used as key.<br>
     * <b>Note:</b> Values will be identical to what a WP_Post object would contain.
     * @see WP_Post
     * @param 'ID'|'post_author'|'post_date'|'post_date_gmt'|'post_content'|'post_title'|'post_excerpt'|'post_status'|'comment_status'|'ping_status'|'post_password'|'post_name'|'to_ping'|'pinged'|'post_modified'|'post_modified_gmt'|'post_content_filtered'|'post_parent'|'guid'|'menu_order'|'post_type'|'post_mime_type'|'comment_count' $column
     * @param ''|'ID'|'post_author'|'post_date'|'post_date_gmt'|'post_content'|'post_title'|'post_excerpt'|'post_status'|'comment_status'|'ping_status'|'post_password'|'post_name'|'to_ping'|'pinged'|'post_modified'|'post_modified_gmt'|'post_content_filtered'|'post_parent'|'guid'|'menu_order'|'post_type'|'post_mime_type'|'comment_count' $indexColumn
     * @return array<string, string>|array<string, int>|array<int, string>|array<int, int>
     */
    final public function pluck(string $column, string $indexColumn = ''): array
    {
        if ($indexColumn) {
            $data = [];

            foreach ($this->only([$column, $indexColumn]) as $post) {
                $data[$post->$indexColumn] = $post->$column;
            }

            return $data;
        }

        return array_map(fn ($post) => $post->$column, $this->only([$column]));
    }
}
