<?php

namespace OffbeatWP\Content\Post;

use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use OffbeatWP\Content\Post\Relations\BelongsTo;
use OffbeatWP\Content\Post\Relations\BelongsToMany;
use OffbeatWP\Content\Post\Relations\HasMany;
use OffbeatWP\Content\Post\Relations\HasOne;
use WP_Post;

/**
 * @method mixed getField() getField(string $selector, bool $format_value = true)
 * @method array getFieldObject() getFieldObject(string $selector, bool $format_value = true)
 * @method bool updateField() updateField(string $selector, mixed $value = false)
 */
class PostModel implements PostModelInterface
{
    private const DEFAULT_POST_STATUS = 'publish';
    private const DEFAULT_COMMENT_STATUS = 'closed';
    private const DEFAULT_PING_STATUS = 'closed';

    /** @var WP_Post|null */
    public $wpPost;
    /** @var array */
    public $metaInput = [];
    /** @var array|false */
    protected $metas = false;

    use Macroable {
        __call as macroCall;
        __callStatic as macroCallStatic;
    }

    /** @param WP_Post|int|null $post */
    public function __construct($post = null)
    {
        if (is_null($post)) {
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

    public static function __callStatic($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return static::macroCallStatic($method, $parameters);
        }

        return static::query()->$method(...$parameters);
    }

    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if (isset($this->wpPost->$method)) {
            return $this->wpPost->$method;
        }

        if (!is_null($hookValue = offbeat('hooks')->applyFilters('post_attribute', null, $method, $this))) {
            return $hookValue;
        }

        if (method_exists(WpQueryBuilderModel::class, $method)) {
            return static::query()->$method(...$parameters);
        }

        return false;
    }

    public function __get($name)
    {
        $methodName = 'get' . str_replace('_', '', ucwords($name, '_'));

        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }

        return null;
    }

    public function __isset($name): bool
    {
        $methodName = 'get' . str_replace('_', '', ucwords($name, '_'));

        if (method_exists($this, $methodName)) {
            return true;
        }

        return false;
    }

    /* Attribute methods */
    public function getId(): ?int
    {
        return $this->wpPost->ID ?? null;
    }

    public function getTitle()
    {
        return apply_filters('the_title', $this->wpPost->post_title, $this->getId());
    }

    public function getContent()
    {
        if ($this->isPasswordRequired()) {
            return get_the_password_form($this->wpPost);
        }

        $content = $this->wpPost->post_content;

        // When the_content filter is already running with Gutenberg content
        // it adds another filter that prevents wpautop to be executed.
        // In this case we manually run a series of filters
        if (has_filter('the_content', '_restore_wpautop_hook')) {
            collect([
                'wptexturize',
                'wpautop',
                'shortcode_unautop',
                'prepend_attachment',
                'wp_make_content_images_responsive',
                'do_shortcode',
            ])->each(function ($filter) use (&$content) {
                if (function_exists($filter)) {
                    $content = $filter($content);
                }
            });

            return $content;
        }

        return apply_filters('the_content', $content);
    }

    public function getPostName(): string
    {
        return $this->wpPost->post_name;
    }

    public function getSlug(): string
    {
        return $this->getPostName();
    }

    public function getPermalink()
    {
        return get_permalink($this->getId());
    }

    public function getPostTypeLabel()
    {
        $postType = get_post_type_object(get_post_type($this->wpPost));

        if (empty($postType) || empty($postType->label)) return false;

        return $postType->label;
    }

    public function getPostType(): string
    {
        return $this->wpPost->post_type;
    }

    public function getPostStatus(): string
    {
        return $this->wpPost->post_status;
    }

    public function isPostType(string $postType): bool
    {
        return $this->getPostType() === $postType;
    }

    public function getPostDate(string $format = '')
    {
        return get_the_date($format, $this->wpPost);
    }

    public function getExcerpt(bool $formatted = true)
    {
        if (!$formatted) {
            return get_the_excerpt($this->wpPost);
        }

        $currentPost = $GLOBALS['post'];

        $GLOBALS['post'] = $this->wpPost;

        ob_start();
        the_excerpt();
        $excerpt = ob_get_contents();
        ob_end_clean();

        $GLOBALS['post'] = $currentPost;

        return $excerpt;
    }

    public function getAuthor()
    {
        $authorId = $this->getAuthorId();

        if (!isset($authorId) || empty($authorId)) return false;

        return get_userdata($authorId);
    }

    public function getAuthorId()
    {
        $authorId = $this->wpPost->post_author;

        if (!isset($authorId) || empty($authorId)) return false;

        return $authorId;
    }

    /** @return false|array */
    public function getMetas()
    {
        if ($this->metas === false) {
            $this->metas = get_post_meta($this->getId());
        }

        return $this->metas;
    }

    public function getMeta(string $key, bool $single = true)
    {
        if (isset($this->getMetas()[$key])) {
            return $single && is_array($this->getMetas()[$key])
                ? reset($this->getMetas()[$key])
                : $this->getMetas()[$key];
        }

        return null;
    }

    public function setMetas(array $metadata): void
    {
        foreach ($metadata as $key => $value) {
            $this->setMeta($key, $value);
        }
    }

    public function setMeta(string $key, $value): PostModel
    {
        $this->metaInput[$key] = $value;

        return $this;
    }

    public function getTerms($taxonomy, $unused = [])
    {
        $model = offbeat('taxonomy')->getModelByTaxonomy($taxonomy);

        return $model::whereRelatedToPost($this->getId());
    }

    /**
     * @param array|int|string $term
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
     * @param array|string $size
     * @param array|string $attr
     * @return string
     */
    public function getFeaturedImage($size = 'thumbnail', $attr = []): string
    {
        return get_the_post_thumbnail($this->wpPost, $size, $attr);
    }

    /**
     * @param array|string $size
     * @return false|string
     */
    public function getFeaturedImageUrl($size = 'thumbnail')
    {
        return get_the_post_thumbnail_url($this->wpPost, $size);
    }

    public function getFeaturedImageId()
    {
        return !empty($id = get_post_thumbnail_id($this->wpPost)) ? $id : false;
    }

    public function setTitle(string $title): void
    {
        $this->wpPost->post_title = $title;
    }

    public function setPostName(string $postName): void
    {
        $this->wpPost->post_name = $postName;
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
        if (!is_null($this->getParentId())) {
            return true;
        }

        return false;
    }

    public function getParent(): ?PostModel
    {
        if (empty($this->getParentId())) {
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

    /** @deprecated Use getChildren instead */
    public function getChilds(): PostsCollection
    {
        return $this->getChildren();
    }

    public function getChildren(): PostsCollection
    {
        return static::query()->where(['post_parent' => $this->getId()])->all();
    }

    /** @return int[] */
    public function getAncestorIds(): array
    {
        return get_post_ancestors($this->getId());
    }

    public function getAncestors(): Collection
    {
        $ancestors = collect();

        if ($this->hasParent()) {
            foreach ($this->getAncestorIds() as $ancestorId) {
                $ancestors->push(offbeat('post')->get($ancestorId));
            }
        }

        return $ancestors;
    }

    public function getPreviousPost(bool $inSameTerm = false, string $excludedTerms = '', string $taxonomy = 'category')
    {
        return $this->getAdjacentPost($inSameTerm, $excludedTerms, true, $taxonomy);
    }

    public function getNextPost(bool $inSameTerm = false, string $excludedTerms = '', string $taxonomy = 'category')
    {
        return $this->getAdjacentPost($inSameTerm, $excludedTerms, false, $taxonomy);
    }

    public function getAdjacentPost(bool $inSameTerm = false, string $excludedTerms = '', bool $previous = true, string $taxonomy = 'category')
    {
        $currentPost = $GLOBALS['post'];

        $GLOBALS['post'] = $this->wpPost;

        $adjacentPost = get_adjacent_post($inSameTerm, $excludedTerms, $previous, $taxonomy);

        $GLOBALS['post'] = $currentPost;

        if (!empty($adjacentPost)) {
            return offbeat('post')->convertWpPostToModel($adjacentPost);
        }

        return false;
    }

    public function isPasswordRequired(): bool
    {
        return post_password_required($this->wpPost);
    }

    /* Display methods */
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

    /* Change methods */
    /** @return false|WP_Post|null */
    public function delete(bool $force = true)
    {
        return wp_delete_post($this->getId(), $force);
    }

    /** @return false|WP_Post|null */
    public function trash()
    {
        return wp_trash_post($this->getId());
    }

    /** @return false|WP_Post|null */
    public function untrash()
    {
        return wp_untrash_post($this->getId());
    }

    public function save(): int
    {
        if (!empty($this->metaInput)) {
            $this->wpPost->meta_input = $this->metaInput;
        }

        if (is_null($this->getId())) {
            $postId = wp_insert_post((array)$this->wpPost);

            $this->wpPost = get_post($postId);

            return $postId;
        } else {
            return wp_update_post($this->wpPost);
        }
    }

    /* Relations */
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

    public function hasMany($key): HasMany
    {
        return new HasMany($this, $key);
    }

    public function hasOne($key): HasOne
    {
        return new HasOne($this, $key);
    }

    public function belongsTo($key): BelongsTo
    {
        return new BelongsTo($this, $key);
    }

    public function belongsToMany($key): BelongsToMany
    {
        return new BelongsToMany($this, $key);
    }

    public static function query(): WpQueryBuilderModel
    {
        return new WpQueryBuilderModel(static::class);
    }
}
