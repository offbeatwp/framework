<?php

namespace OffbeatWP\Content\Post;

use DateTimeZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use OffbeatWP\Content\Post\Relations\BelongsTo;
use OffbeatWP\Content\Post\Relations\BelongsToMany;
use OffbeatWP\Content\Post\Relations\HasMany;
use OffbeatWP\Content\Post\Relations\HasOne;
use OffbeatWP\Content\Taxonomy\TermQueryBuilder;
use OffbeatWP\Content\Traits\BaseModelTrait;
use OffbeatWP\Content\Traits\GetMetaTrait;
use OffbeatWP\Content\Traits\SetMetaTrait;
use OffbeatWP\Exceptions\OffbeatInvalidModelException;
use OffbeatWP\Exceptions\PostMetaNotFoundException;
use OffbeatWP\Support\Wordpress\WpDateTimeImmutable;
use WP_Post;
use WP_Post_Type;

class PostModel implements PostModelInterface
{
    public readonly WP_Post $wpPost;
    protected ?array $metas = null;

    /**
     * @var string[]|null
     * This should be an associative string array<br>
     * The index should represent the metaKey of the field that contains the relation ID(s)<br>
     * The value should the <b>method name</b> of the method on this model that returns a relation object<br>
     * <i>EG:</i> ['meta_key_therapist_id' => 'TherapistRelation']
     * @see Relation
     */
    public ?array $relationKeyMethods = null;

    use BaseModelTrait;
    use SetMetaTrait;
    use GetMetaTrait;
    use Macroable {
        Macroable::__call as macroCall;
        Macroable::__callStatic as macroCallStatic;
    }

    public function __construct(WP_Post $wpPost)
    {
        if ($wpPost->ID <= 0) {
            throw new OffbeatInvalidModelException('Could not construct ' . class_basename($this) . ' with invalid ID: ' . $wpPost->ID);
        }

        $this->wpPost = $wpPost;
    }

    ///////////////
    /// Getters ///
    ///////////////
    /** @return positive-int */
    public function getId(): int
    {
        return $this->wpPost->ID;
    }

    public function getTitle(): string
    {
        return apply_filters('the_title', $this->wpPost->post_title, $this->getId());
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
            _restore_wpautop_hook(null);

            $didManualRestoreWpAutopHook = true;
        }

        $content = apply_filters('the_content', $content);

        if ($didManualRestoreWpAutopHook) {
            $priority = has_filter( 'the_content', 'wpautop' );
            remove_filter( 'the_content', 'wpautop', $priority );
            add_filter( 'the_content', '_restore_wpautop_hook', $priority + 1 );
        }
        
        return $content;
    }

    public function getPostName(): string
    {
        return $this->wpPost->post_name;
    }

    public function getSlug(): string
    {
        return $this->getPostName();
    }

    /**
     * Retrieves the full permalink for the current post or post ID.
     * @return string|null The permalink URL. Null if the post does not exist.
     */
    public function getPermalink(): ?string
    {
        return get_permalink($this->getId()) ?: null;
    }

    public function getPostTypeLabel(): ?string
    {
        $postType = get_post_type_object(get_post_type($this->wpPost));

        if (!$postType || !$postType->label) {
            return null;
        }

        return $postType->label;
    }

    public function getPostType(): string
    {
        return $this->wpPost->post_type;
    }

    public function getPostTypeObject(): ?WP_Post_Type
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

    public function isPostType(string $postType): bool
    {
        return $this->getPostType() === $postType;
    }

    public function getExcerpt(bool $formatted = true): ?string
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

        return $excerpt ?: null;
    }

    public function getAuthorId(): int
    {
        return (int)$this->wpPost->post_author;
    }

    /** @return false|array|string */
    public function getMetas()
    {
        if ($this->metas === null) {
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
     * @deprecated
     * Moves a meta value from one key to another.
     * @param string $oldMetaKey The old meta key. If this key does not exist, the meta won't be moved.
     * @param string $newMetaKey The new meta key. If this key already exists, the meta won't be moved.
     */
    public function moveMetaValue(string $oldMetaKey, string $newMetaKey)
    {
        if ($this->hasMeta($oldMetaKey) && !$this->hasMeta($newMetaKey)) {
            $this->_setMeta($newMetaKey, $this->getMetaValue($oldMetaKey));
            $this->_unsetMeta($oldMetaKey);
        }
    }

    /**
     * @param string $taxonomy
     * @param array $unused
     * @return TermQueryBuilder
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

    public function getThumbnailId(): int
    {
        return (int)get_post_thumbnail_id($this->wpPost);
    }

    public function getParentId(): int
    {
        return $this->wpPost->post_parent;
    }

    public function getParent(): ?PostModel
    {
        return static::find($this->getParentId());
    }

    public function getTopLevelParent(): ?PostModel
    {
        $ancestors = $this->getAncestors();
        return $ancestors->isNotEmpty() ? $ancestors->last() : null;
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
     */
    public function delete(bool $force = true): ?WP_Post
    {
        return wp_delete_post($this->getId(), $force) ?: null;
    }

    /** Move a post or page to the Trash. If Trash is disabled, the post or page is permanently deleted. */
    public function trash(): ?WP_Post
    {
        return wp_trash_post($this->getId()) ?: null;
    }

    /** Restores a post from the Trash. */
    public function untrash(): ?WP_Post
    {
        return wp_untrash_post($this->getId()) ?: null;
    }

    /** Returns a copy of this model. Note: The ID will be set to <i>null</i> and all meta values will be copied into inputMeta. */
    public function replicate(): static
    {
        $copy = clone $this;

        foreach(get_object_taxonomies($this->wpPost) as $taxonomyName) {
            $termIds = $this->getTerms($taxonomyName)->excludeEmpty(false)->ids();
            $copy->termsToSet[] = ['termIds' => $termIds, 'taxonomy' => $taxonomyName, 'append' => false];
        }

        return $copy;
    }

    protected function getBaseClassName(): string
    {
        return str_replace(__NAMESPACE__ . '\\', '', __CLASS__);
    }

    public function refreshMetas()
    {
        $this->metas = false;
        $this->getMetas();
    }

    /////////////////////
    /// Query Methods ///
    /////////////////////
    public function getMethodByRelationKey(string $relationKey): ?string
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

    final public function hasMany(string $relationKey): HasMany
    {
        return new HasMany($this, $relationKey);
    }

    final public function hasOne(string $relationKey): HasOne
    {
        return new HasOne($this, $relationKey);
    }

    final public function belongsTo(string $relationKey): BelongsTo
    {
        return new BelongsTo($this, $relationKey);
    }

    final public function belongsToMany(string $relationKey): BelongsToMany
    {
        return new BelongsToMany($this, $relationKey);
    }

    final public static function current(): ?static
    {
        $post = offbeat('post')->get();
        return ($post instanceof static) ? $post : null;
    }

    /** @return PostsCollection<static> */
    public static function all(): PostsCollection
    {
        return static::query()->take(-1);
    }

    /** @return WpQueryBuilderModel<static> */
    public static function query(): WpQueryBuilderModel
    {
        return new WpQueryBuilderModel(static::class);
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

    /** @pure */
    final public static function from(WP_Post $post): static
    {
        return new static($post);
    }
    
    public function is(WP_Post $post): bool
    {
        return true;
    }
}
