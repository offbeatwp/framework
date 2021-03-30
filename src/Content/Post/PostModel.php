<?php

namespace OffbeatWP\Content\Post;

use Illuminate\Support\Traits\Macroable;
use OffbeatWP\Content\Post\Relations\BelongsTo;
use OffbeatWP\Content\Post\Relations\BelongsToMany;
use OffbeatWP\Content\Post\Relations\HasMany;
use OffbeatWP\Content\Post\Relations\HasOne;
use WP_Error;
use WP_Post;
use WP_User;

class PostModel implements PostModelInterface
{
    public $wpPost;
    public $metaInput = [];

    protected $metas = false;

    use Macroable {
        __call as macroCall;
        __callStatic as macroCallStatic;
    }

    /**
     * PostModel constructor.
     * @param null|mixed $post
     */
    public function __construct($post = null)
    {
        if (is_null($post)) {
            $this->wpPost = (object)[];
            $this->wpPost->post_type = offbeat('post-type')->getPostTypeByModel(static::class);
            $this->wpPost->post_status = 'publish';
            $this->wpPost->comment_status = 'closed';
            $this->wpPost->ping_status = 'closed';
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

    /**
     * @param $method
     * @param $parameters
     * @return array|false|WpQueryBuilder|mixed
     */
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

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name): bool
    {
        $methodName = 'get' . str_replace('_', '', ucwords($name, '_'));

        if (method_exists($this, $methodName)) {
            return true;
        }

        return false;
    }

    /* Attribute methods */

    /**
     * @return int|null
     */
    public function getId()
    {
        return isset($this->wpPost->ID) ? $this->wpPost->ID : null;
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

    /**
     * @return false|string|WP_Error
     */
    public function getPermalink()
    {
        return get_permalink($this->getId());
    }

    /**
     * @return false|string
     */
    public function getPostTypeLabel()
    {
        $postType = get_post_type_object(get_post_type($this->wpPost));

        if(empty($postType) || empty($postType->label)) return false;

        return $postType->label;
    }

    /**
     * @return string
     */
    public function getPostType()
    {
        return $this->wpPost->post_type;
    }

    /**
     * @param $postType
     * @return bool
     */
    public function isPostType($postType)
    {
        return $this->getPostType() == $postType;
    }

    /**
     * @param string $format
     * @return false|string
     */
    public function getPostDate($format = '')
    {
        return get_the_date($format, $this->wpPost);
    }

    /**
     * @param bool $formatted
     * @return false|string
     */
    public function getExcerpt($formatted = true)
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

    /**
     * @return false|WP_User
     */
    public function getAuthor()
    {
        $authorId = $this->getAuthorId();

        if (!isset($authorId) || empty($authorId)) return false;

        return get_userdata($authorId);
    }

    /**
     * @return false|int|string
     */
    public function getAuthorId()
    {

        $authorId = $this->wpPost->post_author;

        if (!isset($authorId) || empty($authorId)) return false;

        return $authorId;
    }

    public function getMetas()
    {
        if ($this->metas === false) {
            $this->metas = get_post_meta($this->getId());
        }
        return $this->metas;
    }

    public function getMeta($key, $single = true)
    {
        if (isset($this->getMetas()[$key])) {
            return $single && is_array($this->getMetas()[$key])
                ? reset($this->getMetas()[$key])
                : $this->getMetas()[$key];
        }
        return null;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setMeta($key, $value)
    {
        $this->metaInput[$key] = $value;

        return $this;
    }

    public function getTerms($taxonomy, $args = [])
    {
        $model = offbeat('taxonomy')->getModelByTaxonomy($taxonomy);

        return $model::whereRelatedToPost($this->getId());
    }

    /**
     * @param $term
     * @param $taxonomy
     * @return bool
     */
    public function hasTerm($term, $taxonomy)
    {
        return has_term($term, $taxonomy, $this->getId());
    }

    /**
     * @return bool
     */
    public function hasFeaturedImage()
    {
        return has_post_thumbnail($this->wpPost);
    }

    /**
     * @param string $size
     * @param array $attr
     * @return string
     */
    public function getFeaturedImage($size = 'thumbnail', $attr = [])
    {
        return get_the_post_thumbnail($this->wpPost, $size, $attr);
    }

    /**
     * @param string $size
     * @return false|string
     */
    public function getFeaturedImageUrl($size = 'thumbnail')
    {
        return get_the_post_thumbnail_url($this->wpPost, $size);
    }

    /**
     * @return false|int
     */
    public function getFeaturedImageId()
    {
        return !empty($id = get_post_thumbnail_id($this->wpPost)) ? $id : false;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->wpPost->post_title = $title;
    }

    /**
     * @param string $postName
     */
    public function setPostName($postName)
    {
        $this->wpPost->post_name = $postName;
    }

    /**
     * @return int|null
     */
    public function getParentId()
    {
        if ($this->wpPost->post_parent) {
            return $this->wpPost->post_parent;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasParent()
    {
        if (!is_null($this->getParentId())) {
            return true;
        }

        return false;
    }

    public function getParent()
    {
        if (empty($this->getParentId())) {
            return null;
        }

        return new static($this->getParentId());
    }

    /**
     * @deprecated Use getChildren instead
     */
    public function getChilds()
    {
        return static::where(['post_parent' => $this->getId()])->all();
    }

    public function getChildren()
    {
        return $this->getChilds();
    }

    /**
     * @return int[]
     */
    public function getAncestorIds()
    {
        return get_post_ancestors($this->getId());
    }

    public function getAncestors()
    {
        $ancestors = collect();

        if ($this->hasParent()) {
            foreach ($this->getAncestorIds() as $ancestorId) {
                $ancestors->push(offbeat('post')->get($ancestorId));
            }
        }

        return $ancestors;
    }

    /**
     * @param false $inSameTerm
     * @param string $excludedTerms
     * @param string $taxonomy
     * @return false|mixed
     */
    public function getPreviousPost($inSameTerm = false, $excludedTerms = '', $taxonomy = 'category')
    {
        return $this->getAdjacentPost($inSameTerm, $excludedTerms, true, $taxonomy);
    }

    /**
     * @param false $inSameTerm
     * @param string $excludedTerms
     * @param string $taxonomy
     * @return mixed
     */
    public function getNextPost($inSameTerm = false, $excludedTerms = '', $taxonomy = 'category')
    {
        return $this->getAdjacentPost($inSameTerm, $excludedTerms, false, $taxonomy);
    }

    /**
     * @param false $inSameTerm
     * @param string $excludedTerms
     * @param bool $previous
     * @param string $taxonomy
     * @return mixed
     */
    public function getAdjacentPost($inSameTerm = false, $excludedTerms = '', $previous = true, $taxonomy = 'category')
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

    /**
     * @return bool
     */
    public function isPasswordRequired()
    {
        return post_password_required($this->wpPost);
    }

    /* Display methods */

    public function setup()
    {
        global $wp_query;

        setup_postdata($this->wpPost);
        $wp_query->in_the_loop = true;
    }

    public function end()
    {
        global $wp_query;

        $wp_query->in_the_loop = false;
    }

    /* Change methods */

    /**
     * @param bool $force
     * @return array|false|WP_Post|null
     */
    public function delete($force = true)
    {
        return wp_delete_post($this->getId(), $force);
    }

    /**
     * @return array|false|WP_Post|null
     */
    public function trash()
    {
        return wp_trash_post($this->getId());
    }

    /**
     * @return array|false|WP_Post|null
     */
    public function untrash()
    {
        return wp_untrash_post($this->getId());
    }

    public function save()
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
