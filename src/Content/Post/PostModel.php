<?php

namespace OffbeatWP\Content\Post;

use DateTimeZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
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
    use Macroable {
        Macroable::__call as macroCall;
        Macroable::__callStatic as macroCallStatic;
    }
    public const POST_TYPE = 'any';

    public const DEFAULT_POST_STATUS = 'publish';
    public const DEFAULT_COMMENT_STATUS = 'closed';
    public const DEFAULT_PING_STATUS = 'closed';

    public WP_Post|stdClass|null $wpPost;
    /** @var array<string, int|float|string|bool|mixed[]|stdClass|\Serializable> */
    protected array $metaInput = [];
    /** @var array<string, ""> */
    protected array $metaToUnset = [];
    /** @var array<string, mixed>|null */
    protected ?array $metas = null;
    /** @var int[][][]|bool[][]|string[][]|int[][] */
    protected array $termsToSet = [];

    /**
     * @var string[]|null
     * This should be an associative string array<br>
     * The index should represent the metaKey of the field that contains the relation ID(s)<br>
     * The value should the <b>method name</b> of the method on this model that returns a relation object<br>
     * <i>EG:</i> ['meta_key_therapist_id' => 'TherapistRelation']
     * @see Relation
     */
    public $relationKeyMethods = null;

    /**
     * @final
     * @param WP_Post|int|null $post
     */
    public function __construct($post = null)
    {
        if ($post === null) {
            $this->wpPost = (object)[];
            $this->wpPost->post_type = static::POST_TYPE;
            $this->wpPost->post_status = static::DEFAULT_POST_STATUS;
            $this->wpPost->comment_status = static::DEFAULT_COMMENT_STATUS;
            $this->wpPost->ping_status = static::DEFAULT_PING_STATUS;
        } elseif ($post instanceof WP_Post) {
            $this->wpPost = $post;
        } elseif (is_numeric($post)) {
            $this->wpPost = get_post($post);
        } else {
            trigger_error('PostModel expects a WP_Post, NULL or integer as argument but got: ' . gettype($post));
        }

        $this->init();
    }

    /** This method is called at the end of the PostModel constructor */
    protected function init(): void
    {
        // Does nothing unless overriden by parent
    }

    /**
     * @param string $method
     * @param mixed[] $parameters
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
     * @param string $method
     * @param mixed[] $parameters
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
            trigger_error('Called WpQueryBuilder method on a model instance through magic method. Please use the static PostModel::query method instead.', E_USER_DEPRECATED);
            return static::query()->$method(...$parameters);
        }

        return false;
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

    /** @return mixed */
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

    /**
     * @param string $taxonomy
     * @param array{} $unused
     * @return TermQueryBuilder<\OffbeatWP\Content\Taxonomy\TermModel>
     */
    public function getTerms($taxonomy, $unused = []): TermQueryBuilder
    {
        $model = offbeat('taxonomy')->getModelByTaxonomy($taxonomy);

        return $model::query()->whereRelatedToPost($this->getId());
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

    /** @return Collection<int, static> Returns the ancestors of a post. */
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

    /** @return static|null */
    public function getPreviousPost(bool $inSameTerm = false, string $excludedTerms = '', string $taxonomy = 'category')
    {
        return $this->getAdjacentPost($inSameTerm, $excludedTerms, true, $taxonomy);
    }

    /** @return static|null */
    public function getNextPost(bool $inSameTerm = false, string $excludedTerms = '', string $taxonomy = 'category')
    {
        return $this->getAdjacentPost($inSameTerm, $excludedTerms, false, $taxonomy);
    }

    /**
     * @private
     * @final
     * @internal You should use <b>getPreviousPost</b> or <b>getNextPost</b>.
     * @return static|null
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

    /** @return non-empty-string|null The page template slug used by this post or <i>null</i> if it is not found. */
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

        foreach(get_object_taxonomies($this->wpPost) as $taxonomyName) {
            $termIds = $this->getTerms($taxonomyName)->excludeEmpty(false)->ids();
            $copy->termsToSet[] = ['termIds' => $termIds, 'taxonomy' => $taxonomyName, 'append' => false];
        }

        return $copy;
    }

    /** Returns the ID of the inserted/updated model, or <b>0</b> on failure */
    public function save(): int
    {
        if ($this->metaInput) {
            $this->wpPost->meta_input = $this->metaInput;
        }

        if ($this->getId() === null) {
            // Insert post
            $updatedPostId = wp_insert_post((array)$this->wpPost);
            $insertedPost = get_post($updatedPostId);

            // Set internal wpPost
            if ($insertedPost instanceof WP_Post) {
                $this->wpPost = $insertedPost;
            }
        } else {
            // Update post
            $updatedPostId = wp_update_post((array)$this->wpPost);

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

        if (defined("{$modelClass}::POST_TYPE")) {
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

    /**
     * Retrieves the current post from the wordpress loop, provided the PostModel is or extends the PostModel class that it is called on.
     * @return static|null
     */
    final public static function current(): ?static
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

    /** @return PostsCollection<int, static> */
    public static function all(): PostsCollection
    {
        return static::query()->take(-1);
    }

    /** @return WpQueryBuilderModel<static> */
    public static function query(): WpQueryBuilderModel
    {
        return new WpQueryBuilderModel(static::class);
    }

    /** @return static */
    final public static function from(WP_Post $wpPost)
    {
        if ($wpPost->ID <= 0) {
            throw new InvalidArgumentException('Cannot create ' . static::class . ' from WP_Post with invalid ID: ' . $wpPost->ID);
        }

        if (defined(static::class . '::POST_TYPE') && !in_array($wpPost->post_type, (array)static::POST_TYPE, true)) {
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
