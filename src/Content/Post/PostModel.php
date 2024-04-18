<?php

namespace OffbeatWP\Content\Post;

use DateTimeZone;
use OffbeatWP\Content\Common\AbstractOffbeatModel;
use OffbeatWP\Content\Post\Relations\BelongsTo;
use OffbeatWP\Content\Post\Relations\BelongsToMany;
use OffbeatWP\Content\Post\Relations\HasMany;
use OffbeatWP\Content\Post\Relations\HasOne;
use OffbeatWP\Content\Taxonomy\TermQueryBuilder;
use OffbeatWP\Exceptions\OffbeatInvalidModelException;
use OffbeatWP\Support\Wordpress\Post;
use OffbeatWP\Support\Wordpress\Taxonomy;
use OffbeatWP\Support\Wordpress\WpDateTimeImmutable;
use RuntimeException;
use WP_Post;
use WP_Post_Type;

class PostModel extends AbstractOffbeatModel
{
    public const POST_TYPE = 'any';

    /**
     * @var string[]|null
     * This should be an associative string array<br>
     * The index should represent the metaKey of the field that contains the relation ID(s)<br>
     * The value should the <b>method name</b> of the method on this model that returns a relation object<br>
     * <i>EG:</i> ['meta_key_therapist_id' => 'TherapistRelation']
     * @see Relation
     */
    public ?array $relationKeyMethods = null;
    protected readonly WP_Post $wpPost;
    private ?array $metas = null;
    /** @var array{permalink?: string, post_type_object?: WP_Post_Type, excerpt?: string, edit_post_link?: string, date?: WpDateTimeImmutable|null, modified?: WpDateTimeImmutable|null} */
    private array $data = [];

    final private function __construct(WP_Post $wpPost)
    {
        if ($wpPost->ID <= 0) {
            throw new OffbeatInvalidModelException('Could not construct PostModel with invalid ID: ' . $wpPost->ID);
        }

        $this->wpPost = $wpPost;
    }

    ///////////////
    /// Getters ///
    ///////////////
    /** @return positive-int */
    final public function getId(): int
    {
        return $this->wpPost->ID;
    }

    final public function getTitle(bool $formatted = true): string
    {
        if ($formatted) {
            return apply_filters('the_title', $this->wpPost->post_title, $this->getId());
        }

        return $this->wpPost->post_title;
    }

    final public function getContent(bool $formatted = true): string
    {
        if (!$formatted) {
            return $this->wpPost->post_content;
        }

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
            $priority = has_filter( 'the_content', 'wpautop' );
            remove_filter( 'the_content', 'wpautop', $priority );
            add_filter( 'the_content', '_restore_wpautop_hook', $priority + 1 );
        }
        
        return $content;
    }

    final public function getPostName(): string
    {
        return $this->wpPost->post_name;
    }

    final public function getSlug(): string
    {
        return $this->getPostName();
    }

    /** Retrieves the full permalink for the current post or post ID. */
    final public function getPermalink(): string
    {
        if (!array_key_exists('permalink', $this->data)) {
            $permalink = get_permalink($this->getId());
            if ($permalink === false) {
                throw new RuntimeException("Failed to get permalink as post {$this->getId()} does not exist!");
            }

            $this->data['permalink'] = $permalink;
        }

        return $this->data['permalink'];
    }

    final public function getPostType(): string
    {
        return $this->wpPost->post_type;
    }

    final public function getPostTypeObject(): ?WP_Post_Type
    {
        if (!array_key_exists('post_type_object', $this->data)) {
            $postType = get_post_type($this->wpPost);
            if (!$postType) {
                throw new RuntimeException("Failed to get post type object from post {$this->getId()} because it has no post type!");
            }

            $this->data['post_type_object'] = get_post_type_object($postType);
        }

        return $this->data['post_type_object'];
    }

    /**
     * @param string $metaKey The ID stored in this meta-field will be used to retrieve the attachment.
     * @return string|null The attachment url or <i>null</i> if the attachment could not be found.
     */
    final public function getAttachmentUrl(string $metaKey): ?string
    {
        $url = wp_get_attachment_url($this->getMeta($metaKey));
        return ($url === false) ? null : $url;
    }

    /**
     * @param string $metaKey
     * @param int[]|string $size Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order). Default 'thumbnail'.
     * @param array $attributes Attributes for the image markup. Default <i>thumbnail<i/>.
     * @return string HTML image element or empty string on failure.
     */
    final public function getAttachmentImage(string $metaKey, $size = 'thumbnail', array $attributes = []): string
    {
        return wp_get_attachment_image($this->getMetaInt($metaKey), $size, false, $attributes);
    }

    final public function getPostStatus(): string
    {
        return $this->wpPost->post_status;
    }

    final public function isPostType(string $postType): bool
    {
        return $this->getPostType() === $postType;
    }

    final public function getExcerpt(bool $formatted = true): ?string
    {
        if (!array_key_exists('excerpt', $this->data)) {
            if ($formatted) {
                $currentPost = $GLOBALS['post'] ?? null;

                $GLOBALS['post'] = $this->wpPost;

                ob_start();
                the_excerpt();
                $excerpt = ob_get_clean();

                $GLOBALS['post'] = $currentPost;

                $this->data['excerpt'] = $excerpt;
            } else {
                $this->data['excerpt'] = get_the_excerpt($this->wpPost);
            }
        }

        return $this->data['excerpt'];
    }

    final public function getAuthorId(): int
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

    final public function getMeta(string $key, bool $single = true): mixed
    {
        if (isset($this->getMetas()[$key])) {
            return $single && is_array($this->getMetas()[$key]) ? reset($this->getMetas()[$key]) : $this->getMetas()[$key];
        }

        return null;
    }

    /** Retrieves the WP Admin edit link for this post. <br>Returns <i>null</i> if the post type does not exist or does not allow an editing UI. */
    final public function getEditLink(): ?string
    {
        if (!array_key_exists('edit_post_link', $this->data)) {
            $this->data['edit_post_link'] = get_edit_post_link($this->getId());
        }

        return $this->data['edit_post_link'];
    }

    final public function getPostDateTime(): ?WpDateTimeImmutable
    {
        if (!array_key_exists('date', $this->data)) {
            $gmt = get_post_datetime($this->getId(), 'date', 'gmt');
            $this->data['date'] = ($gmt) ? WpDateTimeImmutable::make($gmt, new DateTimeZone('UTC')) : null;
        }

        return $this->data['date'];
    }

    final public function getModifiedDateTime(): ?WpDateTimeImmutable
    {
        if (!array_key_exists('modified', $this->data)) {
            $gmt = get_post_datetime($this->getId(), 'modified', 'gmt');
            $this->data['modified'] = ($gmt) ? WpDateTimeImmutable::make($gmt, new DateTimeZone('UTC')) : null;
        }

        return $this->data['modified'];
    }

    final public function getTerms(string $taxonomy): TermQueryBuilder
    {
        return offbeat(Taxonomy::class)->getModelByTaxonomy($taxonomy)::query()->whereRelatedToPost($this->getId());
    }

    /**
     * @param int[]|string[]|int|string $term
     * @param non-empty-string $taxonomy
     * @return bool
     */
    final public function hasTerm(array|int|string $term, string $taxonomy): bool
    {
        return has_term($term, $taxonomy, $this->getId());
    }

    final public function hasThumbnail(): bool
    {
        return has_post_thumbnail($this->wpPost);
    }

    /**
     * @param int[]|string $size Registered image size to retrieve the source for or a flat array of height and width dimensions
     * @param int[]|string[]|string $attr
     * @return string The post thumbnail image tag.
     */
    final public function getFeaturedImage(array|string $size = 'thumbnail', array|string $attr = []): string
    {
        return get_the_post_thumbnail($this->wpPost, $size, $attr);
    }

    /**
     * @param int[]|string $size Registered image size to retrieve the source for or a flat array of height and width dimensions
     * @return null|string The post thumbnail URL or NULL if no image is available. If `$size` does not match any registered image size, the original image URL will be returned.
     */
    final public function getThumbnailUrl(array|string $size = 'thumbnail'): ?string
    {
        $url = get_the_post_thumbnail_url($this->wpPost, $size);
        return ($url === false) ? null : $url;
    }

    final public function getThumbnailId(): int
    {
        $id = get_post_thumbnail_id($this->wpPost);
        if ($id === false) {
            throw new RuntimeException("Failed to get thumbnail ID as post {$this->getId()} does not exist!");
        }

        return get_post_thumbnail_id($this->wpPost);
    }

    final public function getParentId(): int
    {
        return $this->wpPost->post_parent;
    }

    public function getParent(): ?PostModel
    {
        return static::find($this->getParentId());
    }

    /** Retrieves the children of this post. */
    public function getChildren(): PostCollection
    {
        return static::query()->where(['post_parent' => $this->getId()])->get();
    }

    /** @return int[] Retrieves the IDs of the ancestors of a post. */
    final public function getAncestorIds(): array
    {
        return get_post_ancestors($this->getId());
    }

    /** @return static[] Returns the ancestors of a post. */
    public function getAncestors(): array
    {
        $ancestors = [];

        if ($this->getParentId()) {
            foreach ($this->getAncestorIds() as $ancestorId) {
                $ancestor = offbeat(Post::class)->get($ancestorId);
                if ($ancestor instanceof static) {
                    $ancestors[] = $ancestor;
                }
            }
        }

        return $ancestors;
    }

    final public function getPreviousPost(bool $inSameTerm = false, string $excludedTerms = '', string $taxonomy = 'category'): ?static
    {
        return $this->getAdjacentPost($inSameTerm, $excludedTerms, true, $taxonomy);
    }

    final public function getNextPost(bool $inSameTerm = false, string $excludedTerms = '', string $taxonomy = 'category'): ?static
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

        if ($adjacentPost) {
            $post = offbeat(Post::class)->convertWpPostToModel($adjacentPost);

            if ($post instanceof static) {
                return $post;
            }
        }

        return null;
    }

    /**
     * Whether post requires password and correct password has been provided.
     * @return bool <i>false</i> if a password is not required or the correct password cookie is present, <i>true</i> otherwise.
     */
    final public function isPasswordRequired(): bool
    {
        return post_password_required($this->wpPost);
    }

    /** Retrieve protected post password form content. */
    final public function getPasswordForm(): string
    {
        return get_the_password_form($this->wpPost);
    }

    /** Gets the specific template filename for a given post. */
    final public function getPageTemplate(): string
    {
        $slug = get_page_template_slug($this->wpPost);
        if ($slug === false) {
            throw new RuntimeException("Failed to get slug as post {$this->getId()} does not exist!");
        }

        return $slug;
    }

    final public function getPostObject(): WP_Post
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
    final public function refreshMetas()
    {
        $this->metas = null;
        $this->getMetas();
    }

    /////////////////////
    /// Query Methods ///
    /////////////////////
    final public function getMethodByRelationKey(string $relationKey): ?string
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
        $post = offbeat(Post::class)->get();
        return ($post instanceof static) ? $post : null;
    }

    /** @return mixed[] */
    public static function defaultQueryArgs(): array
    {
        return ['posts_per_page' => -1, 'post_status' => PostStatus::PUBLISHED];
    }

    final public function edit(): PostBuilder
    {
        return PostBuilder::update($this->wpPost->ID);
    }

    /** @return PostQueryBuilder<static> */
    final public static function query(): PostQueryBuilder
    {
        return new PostQueryBuilder(static::class);
    }

    /** @pure */
    final public static function from(WP_Post $post): static
    {
        return new static($post);
    }
}
