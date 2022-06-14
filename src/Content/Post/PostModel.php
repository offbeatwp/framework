<?php

namespace OffbeatWP\Content\Post;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use OffbeatWP\Content\Post\Relations\BelongsTo;
use OffbeatWP\Content\Post\Relations\BelongsToMany;
use OffbeatWP\Content\Post\Relations\HasMany;
use OffbeatWP\Content\Post\Relations\HasOne;
use OffbeatWP\Content\Taxonomy\TermQueryBuilder;
use OffbeatWP\Content\Traits\BaseModelTrait;
use OffbeatWP\Content\Traits\GetMetaTrait;
use OffbeatWP\Exceptions\OffbeatInvalidModelException;
use OffbeatWP\Exceptions\PostMetaNotFoundException;
use WP_Error;
use WP_Post;
use WP_Post_Type;
use WP_Term;
use WP_User;

class PostModel implements PostModelInterface
{
    private const DEFAULT_POST_STATUS = 'publish';
    private const DEFAULT_COMMENT_STATUS = 'closed';
    private const DEFAULT_PING_STATUS = 'closed';

    /** @var WP_Post|object|null */
    public $wpPost;
    /** @var array */
    public $metaInput = [];
    /** @var array */
    protected $metaToUnset = [];
    /** @var array|false|string */
    protected $metas = false;
    /** @var int[][][]|bool[][]|string[][]|int[][] */
    protected $termsToSet = [];

    use BaseModelTrait;
    use GetMetaTrait;
    use Macroable {
        Macroable::__call as macroCall;
        Macroable::__callStatic as macroCallStatic;
    }

    /**
     * @final
     * @param WP_Post|int|null $post
     */
    public function __construct($post = null)
    {
        if ($post === null) {
            $this->wpPost = (object)[];
            $this->wpPost->post_type = offbeat('post-type')->getPostTypeByModel(static::class);
            $this->wpPost->post_status = self::DEFAULT_POST_STATUS;
            $this->wpPost->comment_status = self::DEFAULT_COMMENT_STATUS;
            $this->wpPost->ping_status = self::DEFAULT_PING_STATUS;
        } elseif ($post instanceof WP_Post) {
            $this->wpPost = $post;
        } elseif (is_numeric($post)) {
            $this->wpPost = get_post($post);
        }
    }

    /**
     * @param non-empty-string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return static::macroCallStatic($method, $parameters);
        }

        return static::query()->$method(...$parameters);
    }

    /**
     * @param non-empty-string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if (isset($this->wpPost->$method)) {
            return $this->wpPost->$method;
        }

        $hookValue = offbeat('hooks')->applyFilters('post_attribute', null, $method, $this);
        if ($hookValue !== null) {
            return $hookValue;
        }

        if (method_exists(WpQueryBuilderModel::class, $method)) {
            return static::query()->$method(...$parameters);
        }

        return false;
    }

    /**
     * @param non-empty-string $name
     * @return null
     */
    public function __get($name)
    {
        $methodName = 'get' . str_replace('_', '', ucwords($name, '_'));

        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }

        return null;
    }

    /**
     * @param non-empty-string $name
     * @return bool
     */
    public function __isset($name): bool
    {
        $methodName = 'get' . str_replace('_', '', ucwords($name, '_'));

        return method_exists($this, $methodName);
    }

    public function __clone()
    {
        // Gather all metas while we still have the original wpPost reference
        $this->getMetas();
        // Now clone the wpPost reference
        $this->wpPost = clone $this->wpPost;
        // Set ID to null
        $this->setId(null);
        // Since the new post is unsaved, we'll have to add all meta values
        $this->refreshMetaInput(false);
    }

    protected function refreshMetaInput(bool $ignoreLowDashPrefix = true): void
    {
        foreach ($this->getMetaValues($ignoreLowDashPrefix) as $key => $value) {
            if (!array_key_exists($key, $this->metaInput)) {
                $this->setMeta($key, $value);
            }
        }
    }

    ///////////////////////////
    /// Getters and Setters ///
    ///////////////////////////
    public function getId(): ?int
    {
        return $this->wpPost->ID ?? null;
    }

    /** @return string */
    public function getTitle()
    {
        return apply_filters('the_title', $this->wpPost->post_title, $this->getId());
    }

    public function getUnfilteredTitle(): string
    {
        return $this->wpPost->post_title;
    }

    public function getContent(): string
    {
        if ($this->isPasswordRequired()) {
            return $this->getPasswordForm();
        }

        $content = $this->wpPost->post_content;

        // When the_content filter is already running with Gutenberg content
        // it adds another filter that prevents wpautop to be executed.
        // In this case we manually run a series of filters
        if (has_filter('the_content', '_restore_wpautop_hook')) {
            $content = wptexturize($content);
            $content = wpautop($content);
            $content = shortcode_unautop($content);
            $content = prepend_attachment($content);

            // wp_make_content_images_responsive is deprecated, but we want to maintain some pre-5.5 compat
            if (function_exists('wp_filter_content_tags')) {
                $content = wp_filter_content_tags($content);
            } elseif (function_exists('wp_make_content_images_responsive')) {
                /** @noinspection PhpDeprecationInspection This method is deprecated but kept for WP <5.5 support */
                $content = wp_make_content_images_responsive($content);
            }

            $content = do_shortcode($content);

            return $content;
        }

        return apply_filters('the_content', $content);
    }

    /** @return static */
    public function setId(?int $id)
    {
        $this->wpPost->ID = $id;
        return $this;
    }

    /**
     * Set the (unfiltered) post content.
     * @param string $content
     * @return static
     */
    public function setContent(string $content)
    {
        $this->wpPost->post_content = $content;
        return $this;
    }

    /**
     * @param string|string[]|int[]|WP_Term[] $terms An array of terms to set for the post, or a string of terms separated by commas.<br>Hierarchical taxonomies must always pass IDs rather than slugs.
     * @param string $taxonomy Taxonomy name of the term(s) to set.
     * @param bool $append If <i>true</i>, don't delete existing term, just add on. If <i>false</i>, replace the term with the new term. Default <i>false</i>.
     * @return static
     */
    public function setTerms($terms, string $taxonomy, bool $append = false)
    {
        $this->termsToSet[] = ['termIds' => $terms, 'taxonomy' => $taxonomy, 'append' => $append];
        return $this;
    }

    public function getPostName(): string
    {
        return $this->wpPost->post_name;
    }

    public function getSlug(): string
    {
        return $this->getPostName();
    }

    /** @return false|string|WP_Error */
    public function getPermalink()
    {
        return get_permalink($this->getId());
    }

    /** @return false|string */
    public function getPostTypeLabel()
    {
        $postType = get_post_type_object(get_post_type($this->wpPost));

        if (!$postType || !$postType->label) {
            return false;
        }

        return $postType->label;
    }

    public function getPostType(): string
    {
        return $this->wpPost->post_type;
    }

    public function getPostTypeInstance(): ?WP_Post_Type
    {
        return get_post_type_object(get_post_type($this->wpPost));
    }

    /**
     * @param non-empty-string $metaKey The ID stored in this meta-field will be used to retrieve the attachment.
     * @return string|null The attachment url or <i>null</i> if the attachment could not be found.
     */
    public function getAttachmentUrl(string $metaKey): ?string
    {
        return wp_get_attachment_url($this->getMeta($metaKey)) ?: null;
    }

    /**
     * @param string $metaKey
     * @param int[]|string $size Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order). Default 'thumbnail'.
     * @param array $attributes Attributes for the image markup. Default <i>thumbnail<i/>.
     * @return string HTML image element or empty string on failure.
     */
    public function getAttachmentImage(string $metaKey, $size = 'thumbnail', array $attributes = []): string
    {
        return wp_get_attachment_image($this->getMetaInt($metaKey), $size, false, $attributes);
    }

    public function getPostStatus(): string
    {
        return $this->wpPost->post_status;
    }

    /**
     * @see PostStatus
     * @param string $newStatus
     * @return static
     */
    public function setPostStatus(string $newStatus)
    {
        $this->wpPost->post_status = $newStatus;
        return $this;
    }

    public function isPostType(string $postType): bool
    {
        return $this->getPostType() === $postType;
    }

    /**
     * @param string $format
     * @return false|string
     */
    public function getPostDate(string $format = '')
    {
        return get_the_date($format, $this->wpPost);
    }

    public function getModifiedDate(string $format = ''): ?string
    {
        return get_the_modified_date($format, $this->wpPost) ?: null;
    }
    /**
     * @param bool $formatted
     * @return false|string
     */
    public function getExcerpt(bool $formatted = true)
    {
        if (!$formatted) {
            return get_the_excerpt($this->wpPost);
        }

        $currentPost = $GLOBALS['post'] ?? null;

        $GLOBALS['post'] = $this->wpPost;

        ob_start();
        the_excerpt();
        $excerpt = ob_get_clean();

        $GLOBALS['post'] = $currentPost;

        return $excerpt;
    }

    /**
     * @deprecated Consider using getAuthorModel instead
     * @return false|WP_User
     */
    public function getAuthor()
    {
        $authorId = $this->getAuthorId();

        if (!$authorId) {
            return false;
        }

        return get_userdata($authorId);
    }

    /** @return false|int|numeric-string */
    public function getAuthorId()
    {
        $authorId = $this->wpPost->post_author;

        if (!$authorId) {
            return false;
        }

        return $authorId;
    }

    /** @return false|array|string */
    public function getMetas()
    {
        if ($this->metas === false) {
            $this->metas = get_post_meta($this->getId());
        }

        return $this->metas;
    }

    /**
     * @param bool $ignoreLowDashPrefix When true, keys prefixed with '_' are ignored.
     * @return array An array of all values whose key is not prefixed with <i>_</i>
     */
    public function getMetaValues(bool $ignoreLowDashPrefix = true): array
    {
        $values = [];

        foreach ($this->getMetas() as $key => $value) {
            if (!$ignoreLowDashPrefix || $key[0] !== '_') {
                $values[$key] = reset($value);
            }
        }

        return $values;
    }

    /**
     * @param non-empty-string $key
     * @param bool $single
     * @return false|mixed|string|null
     */
    public function getMeta(string $key, bool $single = true)
    {
        if (isset($this->getMetas()[$key])) {
            return $single && is_array($this->getMetas()[$key])
                ? reset($this->getMetas()[$key])
                : $this->getMetas()[$key];
        }

        return null;
    }

    /** @throws OffbeatInvalidModelException */
    public function getCreatedAt(): Carbon
    {
        $creationDate = get_the_date('Y-m-d H:i:s', $this->wpPost);

        if (!$creationDate) {
            throw new OffbeatInvalidModelException('Unable to find the creation date of post with ID: ' . $this->wpPost->ID ?? '?');
        }

        return Carbon::parse($creationDate);
    }

    /** @throws OffbeatInvalidModelException */
    public function getUpdatedAt(): Carbon
    {
        $updateDate = get_the_modified_date('Y-m-d H:i:s', $this->wpPost);

        if (!$updateDate) {
            throw new OffbeatInvalidModelException('Unable to find the update date of post with ID: ' . $this->wpPost->ID ?? '?');
        }

        return Carbon::parse($updateDate);
    }

    /** @throws PostMetaNotFoundException */
    public function getMetaOrFail(string $key): string
    {
        $result = $this->getMeta($key);

        if ($result === null) {
            throw new PostMetaNotFoundException('PostMeta with key ' . $key . ' could not be found on post with ID ' . $this->wpPost->ID);
        }

        return $result;
    }

    /** @return static */
    public function setMetas(iterable $metadata)
    {
        foreach ($metadata as $key => $value) {
            $this->setMeta($key, $value);
        }

        return $this;
    }

    /** @return static */
    public function setMeta(string $key, $value)
    {
        $this->metaInput[$key] = $value;

        unset($this->metaToUnset[$key]);

        return $this;
    }

    /**
     * Moves a meta value from one key to another.
     * @param non-empty-string $oldMetaKey The old meta key. If this key does not exist, the meta won't be moved.
     * @param non-empty-string $newMetaKey The new meta key. If this key already exists, the meta won't be moved.
     */
    public function moveMetaValue(string $oldMetaKey, string $newMetaKey)
    {
        if ($this->hasMeta($oldMetaKey) && !$this->hasMeta($newMetaKey)) {
            $this->setMeta($newMetaKey, $this->getMetaValue($oldMetaKey));
            $this->unsetMeta($oldMetaKey);
        }
    }

    /**
     * @param non-empty-string $key Metadata name.
     * @return static
     */
    public function unsetMeta(string $key)
    {
        $this->metaToUnset[$key] = '';

        unset($this->metaInput[$key]);

        return $this;
    }

    /**
     * @param string $taxonomy
     * @param array $unused
     * @return TermQueryBuilder
     */
    public function getTerms($taxonomy, $unused = []): TermQueryBuilder
    {
        $model = offbeat('taxonomy')->getModelByTaxonomy($taxonomy);

        return $model::whereRelatedToPost($this->getId() ?? [0]);
    }

    /**
     * @param int[]|string[]|int|string $term
     * @param string $taxonomy
     * @return bool
     */
    public function hasTerm($term, string $taxonomy): bool
    {
        return has_term($term, $taxonomy, $this->getId());
    }

    public function hasFeaturedImage(): bool
    {
        return has_post_thumbnail($this->wpPost);
    }

    /**
     * @param int[]|string $size Registered image size to retrieve the source for or a flat array of height and width dimensions
     * @param int[]|string[]|string $attr
     * @return string The post thumbnail image tag.
     */
    public function getFeaturedImage($size = 'thumbnail', $attr = []): string
    {
        return get_the_post_thumbnail($this->wpPost, $size, $attr);
    }

    /**
     * @param int[]|string $size Registered image size to retrieve the source for or a flat array of height and width dimensions
     * @return false|string The post thumbnail URL or false if no image is available. If `$size` does not match any registered image size, the original image URL will be returned.
     */
    public function getFeaturedImageUrl($size = 'thumbnail')
    {
        return get_the_post_thumbnail_url($this->wpPost, $size);
    }

    /** @return false|int */
    public function getFeaturedImageId()
    {
        return get_post_thumbnail_id($this->wpPost) ?: false;
    }

    /** @return static */
    public function setExcerpt(string $excerpt)
    {
        $this->wpPost->post_excerpt = $excerpt;
        return $this;
    }

    /** @return static */
    public function setTitle(string $title)
    {
        $this->wpPost->post_title = $title;
        return $this;
    }

    /** @return static */
    public function setPostName(string $postName)
    {
        $this->wpPost->post_name = $postName;
        return $this;
    }

    public function getParentId(): ?int
    {
        if ($this->wpPost->post_parent) {
            return $this->wpPost->post_parent;
        }

        return null;
    }

    public function hasParent(): bool
    {
        return (bool)$this->getParentId();
    }

    public function getParent(): ?PostModel
    {
        if (!$this->getParentId()) {
            return null;
        }

        return new static($this->getParentId());
    }

    public function getTopLevelParent(): ?PostModel
    {
        $ancestors = $this->getAncestors();
        $this->getAncestors()->last();
        return $ancestors->isNotEmpty() ? $this->getAncestors()->last() : null;
    }

    /**
     * @deprecated Use getChildren instead
     * @see getChildren
     */
    public function getChilds(): PostsCollection
    {
        trigger_error('Deprecated getChilds called in PostModel. Use getChildren instead.', E_USER_DEPRECATED);
        return $this->getChildren();
    }

    /** @return PostsCollection Retrieves the children of this post. */
    public function getChildren()
    {
        return static::query()->where(['post_parent' => $this->getId()])->all();
    }

    /** @return int[] Retrieves the IDs of the ancestors of a post. */
    public function getAncestorIds(): array
    {
        return get_post_ancestors($this->getId());
    }

    /** @return Collection Returns the ancestors of a post. */
    public function getAncestors(): Collection
    {
        $ancestors = collect();

        if ($this->hasParent()) {
            foreach ($this->getAncestorIds() as $ancestorId) {
                $ancestor = offbeat('post')->get($ancestorId);
                if ($ancestor) {
                    $ancestors->push($ancestor);
                }
            }
        }

        return $ancestors;
    }

    /**
     * @param bool $inSameTerm
     * @param string $excludedTerms
     * @param string $taxonomy
     * @return PostModel|null
     */
    public function getPreviousPost(bool $inSameTerm = false, string $excludedTerms = '', string $taxonomy = 'category')
    {
        return $this->getAdjacentPost($inSameTerm, $excludedTerms, true, $taxonomy);
    }

    /**
     * @param bool $inSameTerm
     * @param string $excludedTerms
     * @param string $taxonomy
     * @return PostModel|null
     */
    public function getNextPost(bool $inSameTerm = false, string $excludedTerms = '', string $taxonomy = 'category')
    {
        return $this->getAdjacentPost($inSameTerm, $excludedTerms, false, $taxonomy);
    }

    /**
     * @internal You should use <b>getPreviousPost</b> or <b>getNextPost</b>.
     * @param bool $inSameTerm
     * @param string $excludedTerms
     * @param bool $previous
     * @param string $taxonomy
     * @return PostModel|null
     */
    public function getAdjacentPost(bool $inSameTerm = false, string $excludedTerms = '', bool $previous = true, string $taxonomy = 'category')
    {
        $currentPost = $GLOBALS['post'] ?? null;

        $GLOBALS['post'] = $this->wpPost;

        $adjacentPost = get_adjacent_post($inSameTerm, $excludedTerms, $previous, $taxonomy);

        if ($currentPost !== null) {
            $GLOBALS['post'] = $currentPost;
        }

        if ($adjacentPost) {
            return offbeat('post')->convertWpPostToModel($adjacentPost);
        }

        return null;
    }

    /**
     * Whether post requires password and correct password has been provided.
     * @return bool <i>false</i> if a password is not required or the correct password cookie is present, <i>true</i> otherwise.
     */
    public function isPasswordRequired(): bool
    {
        return post_password_required($this->wpPost);
    }

    /** Retrieve protected post password form content. */
    public function getPasswordForm(): string
    {
        return get_the_password_form($this->wpPost);
    }

    /** @return string The page template slug used by this post or <i>null</i> if it is not found. */
    public function getPageTemplate(): ?string
    {
        return get_page_template_slug($this->wpPost) ?: null;
    }

    /** @return WP_Post|object|null */
    public function getPostObject(): ?object
    {
        return $this->wpPost;
    }

    ///////////////////////
    /// Display Methods ///
    ///////////////////////
    public function setup(): void
    {
        global $wp_query;

        setup_postdata($this->wpPost);
        $wp_query->in_the_loop = true;
    }

    public function end(): void
    {
        global $wp_query;

        $wp_query->in_the_loop = false;
    }

    //////////////////////
    /// Change Methods ///
    //////////////////////
    /**
     * When the post and page is permanently deleted, everything that is tied to it is deleted also.
     * This includes comments, post meta fields, and terms associated with the post.
     * The post is moved to Trash instead of permanently deleted unless Trash is disabled or if it is already in trash.
     * @var bool $force Whether to bypass Trash and force deletion. <i>Default false</i>.
     * @return false|WP_Post|null
     */
    public function delete(bool $force = true)
    {
        return wp_delete_post($this->getId(), $force);
    }

    /**
     * Move a post or page to the Trash. If Trash is disabled, the post or page is permanently deleted.
     * @return false|WP_Post|null
     */
    public function trash()
    {
        return wp_trash_post($this->getId());
    }

    /**
     * Restores a post from the Trash.
     * @return false|WP_Post|null
     */
    public function untrash()
    {
        return wp_untrash_post($this->getId());
    }

    /** @return static Returns a copy of this model. Note: The ID will be set to <i>null</i> and all meta values will be copied into inputMeta. */
    public function replicate()
    {
        $copy = clone $this;

        foreach(get_object_taxonomies($this->wpPost) as $taxonomyName) {
            $termIds = $this->getTerms($taxonomyName)->excludeEmpty(false)->ids();
            $copy->termsToSet[] = ['termIds' => $termIds, 'taxonomy' => $taxonomyName, 'append' => false];
        }

        return $copy;
    }

    public function save(): int
    {
        if ($this->metaInput) {
            $this->wpPost->meta_input = $this->metaInput;
        }

        // Insert the post if ID is null
        if ($this->getId() === null) {
            $insertedPostId = wp_insert_post((array)$this->wpPost);
            $insertedPost = get_post($insertedPostId);

            // Update internal wpPost
            if ($insertedPost instanceof WP_Post) {
                $this->wpPost = $insertedPost;
            }

            $this->attachTerms($insertedPostId);

            return $insertedPostId;
        }

        // Otherwise, update the post
        $updatedPostId = wp_update_post($this->wpPost);

        // Unset Meta
        if ($updatedPostId && is_int($updatedPostId)) {
            foreach ($this->metaToUnset as $keyToUnset => $valueToUnset) {
                delete_post_meta($updatedPostId, $keyToUnset, $valueToUnset);
            }
        }

        // Attach Terms
        $this->attachTerms($updatedPostId);

        return $updatedPostId;
    }

    private function attachTerms(int $id)
    {
        foreach ($this->termsToSet as $term) {
            wp_set_post_terms($id, $term['termIds'], $term['taxonomy'], $term['append']);
        }
    }

    /** @return positive-int */
    public function saveOrFail(): int
    {
        $result = $this->save();

        if ($result <= 0) {
            throw new OffbeatInvalidModelException('Failed to save ' . $this->getBaseClassName());
        }

        return $result;
    }

    protected function getBaseClassName(): string
    {
        return str_replace(__NAMESPACE__ . '\\', '', __CLASS__);
    }

    /** @return string[]|null */
    protected function getRelationKeyMethods(): ?array
    {
        return $this->relationKeyMethods ?? null;
    }

    public function refreshMetas()
    {
        $this->metas = false;
        $this->getMetas();
    }

    /**
     * Retrieves the associated post type object.
     * Only works after the post type has been registered.
     * @return WP_Post_Type|null
     */
    public static function getPostTypeObject(): ?WP_Post_Type
    {
        $modelClass = static::class;

        if (defined("{$modelClass}::POST_TYPE")) {
            return get_post_type_object($modelClass::POST_TYPE);
        }

        return null;
    }

    /////////////////////
    /// Query Methods ///
    /////////////////////
    /**
     * @param string $key
     * @return mixed
     */
    public function getMethodByRelationKey($key)
    {
        $method = $key;

        if (isset($this->relationKeyMethods) && is_array($this->relationKeyMethods) && isset($this->relationKeyMethods[$key])) {
            $method = $this->relationKeyMethods[$key];
        }

        if (method_exists($this, $method)) {
            return $method;
        }

        return null;
    }

    /**
     * @param string $key
     * @return HasMany
     */
    public function hasMany($key): HasMany
    {
        return new HasMany($this, $key);
    }

    /**
     * @param string $key
     * @return HasOne
     */
    public function hasOne($key): HasOne
    {
        return new HasOne($this, $key);
    }

    /**
     * @param string $key
     * @return BelongsTo
     */
    public function belongsTo($key): BelongsTo
    {
        return new BelongsTo($this, $key);
    }

    /**
     * @param string $key
     * @return BelongsToMany
     */
    public function belongsToMany($key): BelongsToMany
    {
        return new BelongsToMany($this, $key);
    }

    /**
     * Retrieves the current post from the wordpress loop, provided the PostModel is or extends the PostModel class that it is called on.
     * @return static|null
     */
    public static function current()
    {
        $post = offbeat('post')->get();
        return ($post instanceof static) ? $post : null;
    }

    /**
     * Create a PostModel with an ID without running get_post.
     * @param int $id Only accepts and ID as parameter.
     * @return static
     */
    public static function createLazy(int $id)
    {
        $model = new static(null);
        $model->setId($id);

        return $model;
    }

    public static function query(): WpQueryBuilderModel
    {
        return new WpQueryBuilderModel(static::class);
    }
}
