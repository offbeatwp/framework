<?php

namespace OffbeatWP\Content\Post;

use DateTimeZone;
use InvalidArgumentException;
use OffbeatWP\Content\Post\Relations\BelongsTo;
use OffbeatWP\Content\Post\Relations\BelongsToMany;
use OffbeatWP\Content\Post\Relations\BelongsToOneOrMany;
use OffbeatWP\Content\Post\Relations\HasMany;
use OffbeatWP\Content\Post\Relations\HasOne;
use OffbeatWP\Content\Post\Relations\HasOneOrMany;
use OffbeatWP\Content\Taxonomy\TermQueryBuilder;
use OffbeatWP\Content\Traits\BaseModelTrait;
use OffbeatWP\Content\Traits\GetMetaTrait;
use OffbeatWP\Content\Traits\SetMetaTrait;
use OffbeatWP\Exceptions\OffbeatInvalidModelException;
use OffbeatWP\Exceptions\PostMetaNotFoundException;
use OffbeatWP\Support\Wordpress\Post;
use OffbeatWP\Support\Wordpress\Taxonomy;
use OffbeatWP\Support\Wordpress\WpDateTimeImmutable;
use stdClass;
use WP_Post;
use WP_Post_Type;
use WP_User;

class PostModel implements PostModelInterface
{
    use BaseModelTrait;
    use SetMetaTrait;
    use GetMetaTrait;

    public const POST_TYPE = 'any';

    public const DEFAULT_POST_STATUS = 'publish';
    public const DEFAULT_COMMENT_STATUS = 'closed';
    public const DEFAULT_PING_STATUS = 'closed';

    public ?WP_Post $wpPost;
    /** @var array<string, int|float|string|bool|mixed[]|stdClass|\Serializable> */
    protected array $metaInput = [];
    /** @var array<string, ""> */
    protected array $metaToUnset = [];
    /** @var array<string, mixed>|null */
    protected ?array $metas = null;
    /** @var int[][][]|bool[][]|string[][]|int[][] */
    protected array $termsToSet = [];

    /**
     * @var array<non-empty-string, non-empty-string>|null
     * This should be an associative string array<br>
     * The index should represent the metaKey of the field that contains the relation ID(s)<br>
     * The value should the <b>method name</b> of the method on this model that returns a relation object<br>
     * <i>EG:</i> ['meta_key_therapist_id' => 'TherapistRelation']
     * @see Relation
     */
    public $relationKeyMethods = null;

    final public function __construct(?WP_Post $post = null)
    {
        if ($post === null) {
            $this->wpPost = new WP_Post((object)[
                'post_type' => static::POST_TYPE,
                'post_status' => static::DEFAULT_POST_STATUS,
                'comment_status' => static::DEFAULT_COMMENT_STATUS,
                'ping_status' => static::DEFAULT_PING_STATUS
            ]);
        } else {
            $this->wpPost = $post;
        }
    }

    /**
     * @param string $name
     * @return mixed
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
     * @param string $name
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
        // do some magic to restore the wpautop and restore the restoreautop afterwards.

        $hasRestoreWpautopHook = has_filter('the_content', '_restore_wpautop_hook');
        $didManualRestoreWpAutopHook = false;
        if ($hasRestoreWpautopHook) {
            _restore_wpautop_hook('');

            $didManualRestoreWpAutopHook = true;
        }

        $content = apply_filters('the_content', $content);

        if ($didManualRestoreWpAutopHook) {
            $priority = has_filter('the_content', 'wpautop');
            remove_filter('the_content', 'wpautop', $priority);
            add_filter('the_content', '_restore_wpautop_hook', $priority + 1);
        }

        return $content;
    }

    /** @return $this */
    public function setId(?int $id)
    {
        $this->wpPost->ID = $id;
        return $this;
    }

    /**
     * Set the (unfiltered) post content.
     * @return $this
     */
    public function setContent(string $content)
    {
        $this->wpPost->post_content = $content;
        return $this;
    }

    /** @return $this */
    public function setAuthor(int $authorId)
    {
        $this->wpPost->post_author = $authorId;
        return $this;
    }

    /**
     * @param string|string[]|int[] $terms An array of terms to set for the post, or a string of term slugs separated by commas.<br>Hierarchical taxonomies must always pass IDs rather than slugs.
     * @param string $taxonomy Taxonomy name of the term(s) to set.
     * @param bool $append If <i>true</i>, don't delete existing term, just add on. If <i>false</i>, replace the term with the new term. Default <i>false</i>.
     * @return $this
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

    /** @return false|string */
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
     * @param string $metaKey The ID stored in this meta-field will be used to retrieve the attachment.
     * @return string|null The attachment url or <i>null</i> if the attachment could not be found.
     */
    public function getAttachmentUrl(string $metaKey): ?string
    {
        return wp_get_attachment_url($this->getMeta($metaKey)) ?: null;
    }

    /**
     * @param int[]|string $size Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order). Default 'thumbnail'.
     * @param array{src?: string, class?: string, alt?: string, srcset?: string, sizes?: string, loading?: string|false, decoding?: string} $attributes Attributes for the image markup.
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
     * @return $this
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

    /** @return false|string */
    public function getPostDate(string $format = '')
    {
        return get_the_date($format, $this->wpPost);
    }

    public function getModifiedDate(string $format = ''): ?string
    {
        return get_the_modified_date($format, $this->wpPost) ?: null;
    }

    /** @return false|string */
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

    public function getAuthorId(): int
    {
        return (int)$this->wpPost->post_author;
    }

    /** @return mixed[] */
    final public function getMetas(): array
    {
        if ($this->metas === null) {
            $this->metas = get_post_meta($this->getId()) ?: [];
        }

        return $this->metas;
    }

    /**
     * @param bool $ignoreLowDashPrefix When true, keys prefixed with '_' are ignored.
     * @return mixed[] An array of all values whose key is not prefixed with <i>_</i>
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

    /** @return ($single is true ? mixed : mixed[]) */
    public function getMeta(string $key, bool $single = true)
    {
        if (isset($this->getMetas()[$key])) {
            return $single && is_array($this->getMetas()[$key])
                ? reset($this->getMetas()[$key])
                : $this->getMetas()[$key];
        }

        return null;
    }

    /** Retrieves the WP Admin edit link for this post. <br>Returns <i>null</i> if the post type does not exist or does not allow an editing UI. */
    public function getEditLink(): ?string
    {
        return get_edit_post_link($this->getId());
    }

    public function getPostDateTime(): ?WpDateTimeImmutable
    {
        if ($this->getId()) {
            $gmt = get_post_datetime($this->getId(), 'date', 'gmt');

            if ($gmt) {
                return WpDateTimeImmutable::make($gmt, new DateTimeZone('UTC'));
            }
        }

        return null;
    }

    public function getModifiedDateTime(): ?WpDateTimeImmutable
    {
        if ($this->getId()) {
            $gmt = get_post_datetime($this->getId(), 'modified', 'gmt');

            if ($gmt) {
                return WpDateTimeImmutable::make($gmt, new DateTimeZone('UTC'));
            }
        }

        return null;
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

    /**
     * @param iterable<string, int|float|bool|string|mixed[]|\Serializable|stdClass> $metadata
     * @return $this
     */
    public function setMetas(iterable $metadata)
    {
        foreach ($metadata as $key => $value) {
            $this->setMeta($key, $value);
        }

        return $this;
    }

    /**
     * Moves a meta value from one key to another.
     * @param string $oldMetaKey The old meta key. If this key does not exist, the meta won't be moved.
     * @param string $newMetaKey The new meta key. If this key already exists, the meta won't be moved.
     * @return void
     */
    public function moveMetaValue(string $oldMetaKey, string $newMetaKey)
    {
        if ($this->hasMeta($oldMetaKey) && !$this->hasMeta($newMetaKey)) {
            $this->setMeta($newMetaKey, $this->getMetaValue($oldMetaKey));
            $this->unsetMeta($oldMetaKey);
        }
    }

    /** @return TermQueryBuilder<\OffbeatWP\Content\Taxonomy\TermModel> */
    public function getTerms(string $taxonomy): TermQueryBuilder
    {
        $model = Taxonomy::getInstance()->getModelByTaxonomy($taxonomy);

        return $model::query()->whereRelatedToPost($this->getId());
    }

    /** @param int[]|string[]|int|string $term */
    public function hasTerm(array|int|string $term, string $taxonomy): bool
    {
        return has_term($term, $taxonomy, $this->getId());
    }

    public function hasFeaturedImage(): bool
    {
        return has_post_thumbnail($this->wpPost);
    }

    /**
     * @param array{0: positive-int, 1: positive-int}|string $size Registered image size to retrieve the source for or a flat array of height and width dimensions
     * @param int[]|string[]|string $attr
     * @return string The post thumbnail image tag.
     */
    public function getFeaturedImage(array|string $size = 'thumbnail', array|string $attr = []): string
    {
        return get_the_post_thumbnail($this->wpPost, $size, $attr);
    }

    /**
     * @param array{0: positive-int, 1: positive-int}|string $size Registered image size to retrieve the source for or a flat array of height and width dimensions
     * @return false|string The post thumbnail URL or false if no image is available. If `$size` does not match any registered image size, the original image URL will be returned.
     */
    public function getFeaturedImageUrl($size = 'thumbnail')
    {
        return get_the_post_thumbnail_url($this->wpPost, $size);
    }

    public function getFeaturedImageId(): int
    {
        return (int)get_post_thumbnail_id($this->wpPost);
    }

    /** @return $this */
    public function setExcerpt(string $excerpt)
    {
        $this->wpPost->post_excerpt = $excerpt;
        return $this;
    }

    /** @return $this */
    public function setTitle(string $title)
    {
        $this->wpPost->post_title = $title;
        return $this;
    }

    /** @return $this */
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
        return (bool)$this->wpPost->post_parent;
    }

    public function getParent(): ?PostModel
    {
        $parentId = $this->getParentId();

        if ($parentId) {
            $parent = get_post($parentId);

            if ($parent) {
                return new static($parent);
            }
        }

        return null;
    }

    public function getTopLevelParent(): ?PostModel
    {
        $ancestors = $this->getAncestors();
        return end($ancestors) ?: null;
    }

    /** @return PostsCollection<int, static> Retrieves the children of this post. */
    public function getChildren()
    {
        return static::query()->where(['post_parent' => $this->getId()])->all();
    }

    /** @return int[] Retrieves the IDs of the ancestors of a post. */
    public function getAncestorIds(): array
    {
        return get_post_ancestors($this->getId());
    }

    /** @return list<\OffbeatWP\Content\Post\PostModel> Returns the ancestors of a post. */
    public function getAncestors(): array
    {
        $ancestors = [];

        if ($this->hasParent()) {
            foreach ($this->getAncestorIds() as $ancestorId) {
                $ancestor = Post::getInstance()->get($ancestorId);
                if ($ancestor) {
                    $ancestors[] = $ancestor;
                }
            }
        }

        return $ancestors;
    }

    public function getPreviousPost(bool $inSameTerm = false, string $excludedTerms = '', string $taxonomy = 'category'): ?static
    {
        return $this->getAdjacentPost($inSameTerm, $excludedTerms, true, $taxonomy);
    }

    public function getNextPost(bool $inSameTerm = false, string $excludedTerms = '', string $taxonomy = 'category'): ?static
    {
        return $this->getAdjacentPost($inSameTerm, $excludedTerms, false, $taxonomy);
    }

    private function getAdjacentPost(bool $inSameTerm = false, string $excludedTerms = '', bool $previous = true, string $taxonomy = 'category'): ?static
    {
        $currentPost = $GLOBALS['post'] ?? null;

        $GLOBALS['post'] = $this->wpPost;

        $adjacentPost = get_adjacent_post($inSameTerm, $excludedTerms, $previous, $taxonomy);

        if ($currentPost !== null) {
            $GLOBALS['post'] = $currentPost;
        }

        return $adjacentPost ? static::from($adjacentPost) : null;
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

    /** @return non-empty-string|null The page template slug used by this post or <i>null</i> if it is not found. */
    public function getPageTemplate(): ?string
    {
        return get_page_template_slug($this->wpPost) ?: null;
    }

    /** Get the <b>raw</b> post object */
    public function getPostObject(): ?WP_Post
    {
        return $this->wpPost;
    }

    /** Get the post object as WP_Post */
    final public function getWpPost(): WP_Post
    {
        return $this->wpPost ?: new WP_Post((object)[]);
    }

    ///////////////////////
    /// Display Methods ///
    ///////////////////////
    public function setup(): void
    {
        global $wp_query;

        setup_postdata($this->wpPost);
        $wp_query->in_the_loop = true;

        // Small workaround for a notice the occurs within WP caching featured images.
        if ($wp_query->posts === null) {
            $wp_query->posts = [];
        }
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
     * @param bool $force Whether to bypass Trash and force deletion. <i>Default false</i>.
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

    /** Returns a copy of this model. Note: The ID will be set to <i>null</i> and all meta values will be copied into inputMeta. */
    public function replicate(): self
    {
        $copy = clone $this;

        foreach (get_object_taxonomies($this->wpPost) as $taxonomyName) {
            $termIds = $this->getTerms($taxonomyName)->excludeEmpty(false)->ids();
            $copy->termsToSet[] = ['termIds' => $termIds, 'taxonomy' => $taxonomyName, 'append' => false];
        }

        return $copy;
    }

    /** Returns the ID of the inserted/updated model, or <b>0</b> on failure */
    public function save(): int
    {
        $postData = (array)$this->wpPost;

        if ($this->metaInput) {
            $postData['meta_input'] = $this->metaInput;
        }

        if ($this->getId() === null) {
            // Insert post
            $updatedPostId = wp_insert_post($postData);
            $insertedPost = get_post($updatedPostId);

            // Set internal wpPost
            if ($insertedPost instanceof WP_Post) {
                $this->wpPost = $insertedPost;
            }
        } else {
            // Update post
            $updatedPostId = wp_update_post($postData);

            // Unset Meta
            if ($updatedPostId && is_int($updatedPostId)) {
                foreach ($this->metaToUnset as $keyToUnset => $valueToUnset) {
                    delete_post_meta($updatedPostId, $keyToUnset, $valueToUnset);
                }
            }
        }

        if ($updatedPostId) {
            // Attach Terms to post
            $this->attachTerms($updatedPostId);

            // Update the relations
            foreach (array_keys($this->metaInput + $this->metaToUnset) as $key) {
                $this->updateRelation($key);
            }
        }

        return $updatedPostId;
    }

    private function attachTerms(int $id): void
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

    /**
     * @deprecated
     * @return string[]|null
     */
    protected function getRelationKeyMethods(): ?array
    {
        return $this->relationKeyMethods ?? null;
    }

    /** @return void */
    public function refreshMetas()
    {
        $this->metas = null;
        $this->getMetas();
    }

    /**
     * Retrieves the associated post type object.
     * Only works after the post type has been registered.
     */
    public static function getPostTypeObject(): ?WP_Post_Type
    {
        $modelClass = static::class;

        if (static::POST_TYPE !== 'any') {
            return get_post_type_object($modelClass::POST_TYPE);
        }

        return null;
    }

    /////////////////////
    /// Query Methods ///
    /////////////////////
    /**
     * @param string $relationKey
     * @return null|string
     */
    public function getMethodByRelationKey($relationKey)
    {
        $method = $relationKey;

        if (is_array($this->relationKeyMethods) && isset($this->relationKeyMethods[$relationKey])) {
            $method = $this->relationKeyMethods[$relationKey];
        }

        if (method_exists($this, $method)) {
            return $method;
        }

        return null;
    }

    /** @param string $relationKey */
    public function hasMany($relationKey): HasMany
    {
        return new HasMany($this, $relationKey);
    }

    /** @param string $relationKey */
    public function hasOne($relationKey): HasOne
    {
        return new HasOne($this, $relationKey);
    }

    /** @param string $relationKey */
    public function belongsTo($relationKey): BelongsTo
    {
        return new BelongsTo($this, $relationKey);
    }

    /** @param string $relationKey */
    public function belongsToMany($relationKey): BelongsToMany
    {
        return new BelongsToMany($this, $relationKey);
    }

    /** Retrieves the current post from the wordpress loop, provided the PostModel is or extends the PostModel class that it is called on. */
    final public static function current(): ?static
    {
        $post = Post::getInstance()->get();
        return $post instanceof static ? $post : null;
    }

    /** @return PostsCollection<int, static> */
    public static function all(): PostsCollection
    {
        return static::query()->take(-1);
    }

    /** @return WpQueryBuilder<static> */
    public static function query(): WpQueryBuilder
    {
        return new WpQueryBuilder(static::class);
    }

    /** @return static */
    final public static function from(WP_Post $wpPost)
    {
        if ($wpPost->ID <= 0) {
            throw new InvalidArgumentException('Cannot create ' . static::class . ' from WP_Post with invalid ID: ' . $wpPost->ID);
        }

        if (!in_array($wpPost->post_type, (array)static::POST_TYPE, true) && static::POST_TYPE !== 'any') {
            throw new InvalidArgumentException('Cannot create ' . static::class . ' from WP_Post object: Invalid Post Type');
        }

        return new static($wpPost);
    }

    /** @return int[] Retrieves the value of a meta field as an array of IDs. */
    private function getMetaRelationIds(string $key): array
    {
        $value = get_post_meta($this->getId(), $key, true);

        if (is_serialized($value)) {
            $value = unserialize($value, ['allowed_classes' => false]);
        }

        if (is_array($value)) {
            return array_map('intval', $value);
        }

        if (is_numeric($value)) {
            return [(int)$value];
        }

        return [];
    }

    private function updateRelation(string $key): void
    {
        $method = $this->getMethodByRelationKey($key);

        if ($method) {
            $relation = $this->$method();

            if ($relation) {
                $ids = $this->getMetaRelationIds($key);

                if ($ids && $relation instanceof HasOneOrMany) {
                    $relation->attach($ids, false);
                } elseif ($ids && $relation instanceof BelongsToOneOrMany) {
                    $relation->associate($ids, false);
                } elseif ($relation instanceof HasOneOrMany) {
                    $relation->detachAll();
                } elseif ($relation instanceof BelongsToOneOrMany) {
                    $relation->dissociateAll();
                }
            }
        }
    }
}
