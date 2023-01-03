<?php

namespace OffbeatWP\Content\Post;

use BadMethodCallException;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use OffbeatWP\Content\Common\AbstractOffbeatModel;
use OffbeatWP\Content\Post\Relations\BelongsTo;
use OffbeatWP\Content\Post\Relations\BelongsToMany;
use OffbeatWP\Content\Post\Relations\HasMany;
use OffbeatWP\Content\Post\Relations\HasOne;
use OffbeatWP\Content\Taxonomy\TermQueryBuilder;
use OffbeatWP\Content\User\UserModel;
use OffbeatWP\Exceptions\PostMetaNotFoundException;
use WP_Post;

class PostModel extends AbstractOffbeatModel
{
    public const POST_TYPE = [];
    private const DEFAULT_POST_STATUS = 'publish';
    private const DEFAULT_COMMENT_STATUS = 'closed';
    private const DEFAULT_PING_STATUS = 'closed';

    public ?WP_Post $wpPost;
    public array $relationKeyMethods = [];

    use Macroable {
        Macroable::__call as macroCall;
        Macroable::__callStatic as macroCallStatic;
    }

    /**
     * Expected to return an array of post-types that this model represents.<br>
     * If not set, will use the value of the POST_TYPE constant on this class.
     * @return non-empty-string[]
     */
    public static function postType(): array
    {
        return static::POST_TYPE;
    }

    /** @param WP_Post|int|null $post */
    public function __construct($post = null)
    {
        if ($post === null) {
            $this->wpPost = new WP_Post((object)[]);
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
    public function __call(string $method, array $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if (isset($this->wpPost->$method)) {
            return $this->wpPost->$method;
        }

        throw new BadMethodCallException('Call to undefined method: ' . class_basename($this::class) . ':' . $method);
    }

    /**
     * @param non-empty-string $name
     * @return null
     */
    public function __get(string $name)
    {
        $methodName = 'get' . str_replace('_', '', ucwords($name, '_'));

        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }

        throw new BadMethodCallException('Call to undefined method: ' . class_basename($this::class) . ':' . $name);
    }

    /**
     * @param non-empty-string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        $methodName = 'get' . str_replace('_', '', ucwords($name, '_'));

        return method_exists($this, $methodName);
    }

    public function __clone()
    {
        // Gather all metas while we still have the original wpPost reference
        $this->getMetaData();
        // Now clone the wpPost reference
        $this->wpPost = clone $this->wpPost;
        // Set ID to null
        $this->disconnect();
        // Since the new post is unsaved, we'll have to add all meta values
        $this->refreshMetaInput();
    }

    protected function refreshMetaInput(): void
    {
        foreach ($this->getMetaValues() as $key => $value) {
            if (!array_key_exists($key, $this->metaToSet)) {
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

    public function getTitle(): string
    {
        return (string)apply_filters('the_title', $this->wpPost->post_title, $this->getId());
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
    protected function disconnect()
    {
        $this->wpPost->ID = null;
        $this->metaData = [];
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

    public function getPostName(): string
    {
        return $this->wpPost->post_name;
    }

    public function getSlug(): string
    {
        return $this->getPostName();
    }

    public function getPermalink(): string
    {
        return get_permalink($this->getId()) ?: '';
    }

    public function getPostTypeLabel(): ?string
    {
        $postType = get_post_type_object($this->getPostType());

        if (!$postType || !$postType->label) {
            return null;
        }

        return $postType->label;
    }

    public function getPostType(): string
    {
        return $this->wpPost->post_type;
    }

    /**
     * @param non-empty-string $metaKey The ID stored in this meta-field will be used to retrieve the attachment.
     * @return string|null The attachment url or <i>null</i> if the attachment could not be found.
     */
    public function getAttachmentUrl(string $metaKey): ?string
    {
        return wp_get_attachment_url($this->getMeta($metaKey)) ?: null;
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

    public function getPostDate(string $format = ''): string
    {
        return get_the_date($format, $this->wpPost) ?: '';
    }

    public function getExcerpt(bool $formatted = true): string
    {
        if (!$formatted) {
            return get_the_excerpt($this->wpPost) ?: '';
        }

        $currentPost = $GLOBALS['post'] ?? null;

        $GLOBALS['post'] = $this->wpPost;

        ob_start();
        the_excerpt();
        $excerpt = ob_get_clean() ?: '';

        $GLOBALS['post'] = $currentPost;

        return $excerpt;
    }

    public function getAuthor(): ?UserModel
    {
        return UserModel::find($this->getId() ?: 0);
    }

    public function getAuthorId(): ?int
    {
        $authorId = $this->wpPost->post_author;

        if (!$authorId) {
            return null;
        }

        return (int)$authorId;
    }

    public function getMetaData(): array
    {
        if ($this->metaData === null) {
            $this->metaData = get_post_meta($this->getId()) ?: [];
        }

        return $this->metaData;
    }

    /** @return array An array of all values whose key is not prefixed with <i>_</i> */
    public function getMetaValues(): array
    {
        $values = [];

        foreach ($this->getMetaData() as $key => $value) {
            if ($key[0] !== '_') {
                $values[$key] = reset($value);
            }
        }

        return $values;
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

    /** @deprecated  */
    public function setMetaData(iterable $metadata)
    {
        foreach ($metadata as $key => $value) {
            $this->setMeta($key, $value);
        }

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

        return $model::whereRelatedToPost($this->getId());
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

    public function getPreviousPost(string $taxonomy = '', string $excludedTerms = ''): ?PostModel
    {
        return $this->getAdjacentPost($taxonomy, $excludedTerms, true);
    }

    public function getNextPost(string $taxonomy = '', string $excludedTerms = ''): ?PostModel
    {
        return $this->getAdjacentPost($taxonomy, $excludedTerms, false);
    }

    /** @internal You should use <b>getPreviousPost</b> or <b>getNextPost</b>. */
    private function getAdjacentPost(string $taxonomy, string $excludedTerms, bool $previous): ?PostModel
    {
        $currentPost = $GLOBALS['post'];

        $GLOBALS['post'] = $this->wpPost;

        $adjacentPost = get_adjacent_post((bool)$taxonomy, $excludedTerms, $previous, $taxonomy);

        $GLOBALS['post'] = $currentPost;

        return ($adjacentPost) ? offbeat('post')->convertWpPostToModel($adjacentPost) : null;
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

    /** The page template slug used by this post or <i>null</i> if it is not found. */
    public function getPageTemplate(): ?string
    {
        return get_page_template_slug($this->wpPost) ?: null;
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
     * @var bool $force Whether to bypass Trash and force deletion. <i>Default true</i>.
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
        return clone $this;
    }

    public function save(): int
    {
        if ($this->metaToSet) {
            $this->wpPost->meta_input = $this->metaToSet;
        }

        // Insert the post if ID is null
        if ($this->getId() === null) {
            $insertedPostId = wp_insert_post((array)$this->wpPost);
            $insertedPost = get_post($insertedPostId);

            // Update internal wpPost
            if ($insertedPost instanceof WP_Post) {
                $this->wpPost = $insertedPost;
            }

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

        return $updatedPostId;
    }

    /** @return string[] */
    protected function getRelationKeyMethods(): array
    {
        return $this->relationKeyMethods;
    }

    public function refreshMetas(): void
    {
        $this->metaData = null;
        $this->getMetaData();
    }

    /////////////////////
    /// Query Methods ///
    /////////////////////
    public function getMethodByRelationKey(string $key): ?string
    {
        $methodName = $this->relationKeyMethods[$key] ?? $key;

        if (method_exists($this, $methodName)) {
            return $methodName;
        }

        return null;
    }

    public function hasMany(string $key): HasMany
    {
        return new HasMany($this, $key);
    }

    public function hasOne(string $key): HasOne
    {
        return new HasOne($this, $key);
    }

    public function belongsTo(string $key): BelongsTo
    {
        return new BelongsTo($this, $key);
    }

    public function belongsToMany(string $key): BelongsToMany
    {
        return new BelongsToMany($this, $key);
    }

    /** Retrieves the current post from the wordpress loop, provided the PostModel is or extends the PostModel class that it is called on. */
    public static function current(): ?static
    {
        $post = offbeat('post')->get();
        return ($post instanceof static) ? $post : null;
    }

    public static function query(): PostQueryBuilder
    {
        return new PostQueryBuilder(static::class);
    }
}
