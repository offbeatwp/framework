<?php
namespace OffbeatWP\Content\Post;

use OffbeatWP\Content\Post\Relations\BelongsTo;
use OffbeatWP\Content\Post\Relations\BelongsToMany;
use OffbeatWP\Content\Post\Relations\HasMany;
use OffbeatWP\Content\Post\Relations\HasOne;

class PostModel implements PostModelInterface
{
    public $wpPost;
    public $id;

    protected $metas = false;

    use Macroable {
        __call as macroCall;
        __callStatic as macroCallStatic;
    }

    public function __construct($post)
    {
        if ($post instanceof \WP_Post) {
            $this->wpPost = $post;
        } elseif (is_numeric($post)) {
            $this->wpPost = get_post($post);
        }

        if (isset($this->wpPost)) {
            $this->id = $this->wpPost->ID;
        }
    }

    public static function __callStatic($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return static::macroCallStatic($method, $parameters);
        }

        return (new WpQueryBuilderModel(static::class))->$method(...$parameters);
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

        return false;
    }

    /* Attribute methods */

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return apply_filters('the_title', $this->wpPost->post_title, $this->id);
    }

    public function getContent()
    {
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

    public function getSlug()
    {
        return $this->wpPost->post_name;
    }

    public function getPermalink()
    {
        return get_permalink($this->id);
    }

    public function getPostDate($format = '')
    {
        return get_the_date($format, $this->wpPost);
    }

    public function getExcerpt()
    {
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
        $currentPost     = $GLOBALS['post'];
        $GLOBALS['post'] = $this->wpPost;

        $author = get_the_author();

        $GLOBALS['post'] = $currentPost;

        return $author;
    }

    public function getMetas()
    {
        if ($this->metas === false) {
            $this->metas = get_post_meta($this->id);
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

    public function setMeta($key, $value)
    {
        return update_post_meta($this->id, $key, $value);
    }

    public function getTerms($taxonomy, $args = [])
    {
        $model = offbeat('taxonomy')->getModelByTaxonomy($taxonomy);

        return $model::whereRelatedToPost($this->id)->get();
    }

    public function hasFeaturedImage()
    {
        return has_post_thumbnail($this->wpPost);
    }

    public function getFeaturedImage($size = 'thumbnail', $attr = [])
    {
        return get_the_post_thumbnail($this->wpPost, $size, $attr);
    }

    public function getFeaturedImageUrl($size = 'thumbnail')
    {
        return get_the_post_thumbnail_url($this->wpPost, $size);
    }

    public function getFeaturedImageId()
    {
        return get_post_thumbnail_id($this->wpPost);
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

    public function delete($force = true)
    {
        return wp_delete_post($this->id, $force);
    }

    public function save()
    {
        return wp_update_post($this->wpPost);
    }

    /* Relations */

    public function getMethodByRelationKey($key)
    {
        $method = $key;

        if (isset($this->relationKeyMethods) && is_array($this->relationKeyMethods) && isset($this->relationKeyMethods[$key])) {
            $method = $this->relationKeyMethods[$key];
        }

        if (is_callable([$this, $method])) {
            return $method;
        }

        return null;
    }

    public function hasMany($key)
    {
        return new HasMany($this, $key);
    }

    public function hasOne($key)
    {
        return new HasOne($this, $key);
    }

    public function belongsTo($key)
    {
        return new BelongsTo($this, $key);
    }

    public function belongsToMany($key)
    {
        return new BelongsToMany($this, $key);
    }

}
