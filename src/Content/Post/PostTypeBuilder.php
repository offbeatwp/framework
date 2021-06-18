<?php

namespace OffbeatWP\Content\Post;

use Illuminate\Support\Traits\Macroable;

class PostTypeBuilder
{
    use Macroable;

    /** @var string|null */
    private $postType = null;
    /** @var array */
    private $args = [];
    /** @var class-string|null */
    private $modelClass = null;

    public function make(string $postType, string $pluralName, string $singularLabel): PostTypeBuilder
    {
        $this->postType = $postType;
        $this->args = [
            'labels' => ['name' => $pluralName, 'singular_name' => $singularLabel],
        ];

        return $this;
    }

    public function getPostType(): ?string
    {
        return $this->postType;
    }

    public function isHierarchical(bool $hierarchical = true): PostTypeBuilder
    {
        $this->args['hierarchical'] = $hierarchical;

        return $this;
    }

    /**
     * Triggers the handling of rewrites for this post type
     * @param false|int[]|string[]|bool[] $rewrite Valid array keys are 'slug', 'with_front', 'feeds', 'pages' and 'ep_mask'
     */
    public function rewrite($rewrite): PostTypeBuilder
    {
        if ($rewrite === false) {
            $this->preventRewrites();
        } elseif (is_array($rewrite)) {
            if (!isset($this->args['rewrite'])) {
                $this->args['rewrite'] = [];
            }

            $this->args['rewrite'] = array_merge($this->args['rewrite'], $rewrite);
        }

        return $this;
    }

    /** Prevent URL rewrites for this post type */
    public function preventRewrites() {
        $this->args['rewrite'] = false;
    }

    /** @param string[] $labels */
    public function labels(array $labels): PostTypeBuilder
    {
        if (!isset($this->args['labels'])) {
            $this->args['labels'] = [];
        }

        $this->args['labels'] = array_merge($this->args['labels'], $labels);

        return $this;
    }

    public function model(string $modelClass): PostTypeBuilder
    {
        $this->modelClass = $modelClass;

        return $this;
    }

    /**
     * Adds a new column to the admin table of this custom post type.
     * @param string $slug The slug of the new column
     * @param string $label The label of the new column
     * @param string $modelFunc The name of the function within the Post Type's associated model that should provide the data
     */
    public function addAdminTableColumn(string $slug, string $label, string $modelFunc): PostTypeBuilder
    {
        add_action("manage_{$this->postType}_posts_columns", function(array $postColumns) use ($label, $slug) {
            $postColumns[$slug] = $label;
            return $postColumns;
        });

        add_action("manage_{$this->postType}_posts_custom_column", function(string $columnName, int $postId) use ($slug, $modelFunc) {
            if ($columnName === $slug) {
                $model = new $this->modelClass($postId);
                echo $model->$modelFunc();
            }
        }, 10, 2);

        return $this;
    }

    /** @param string[]|false $support Valid values: ‘title’ ‘editor’ ‘author’ ‘thumbnail’ ‘excerpt’ ‘trackbacks’ ‘custom-fields’ ‘comments’ ‘revisions’ ‘page-attributes’ ‘post-formats’ */
    public function supports($support): PostTypeBuilder
    {
        if (!isset($this->args['supports'])) {
            $this->args['supports'] = [];
        }

        if (is_array($support)) {
            $this->args['supports'] = $this->args['supports'] + $support;
        } else {
            array_push($this->args['supports'], $support);
        }

        return $this;
    }

    public function notPubliclyQueryable(): PostTypeBuilder
    {
        $this->args['publicly_queryable'] = false;

        return $this;
    }

    public function public(bool $public = true): PostTypeBuilder
    {
        $this->args['public'] = $public;

        return $this;
    }

    public function excludeFromSearch(bool $exclude = true): PostTypeBuilder
    {
        $this->args['exclude_from_search'] = $exclude;

        return $this;
    }

    public function showUI(bool $showUi = true): PostTypeBuilder
    {
        $this->args['show_ui'] = $showUi;

        return $this;
    }

    public function icon(string $icon): PostTypeBuilder
    {
        $this->args['menu_icon'] = $icon;

        return $this;
    }

    /** @param bool|string $menu If false, no menu is shown. If a string of an existing top level menu, the post type will be placed as a sub-menu of that. */
    public function inMenu($menu): PostTypeBuilder
    {
        $this->args['show_in_menu'] = $menu;

        return $this;
    }

    public function taxonomies(array $taxonomies): PostTypeBuilder
    {
        $this->args['taxonomies'] = $taxonomies;

        return $this;
    }

    public function inRest(bool $showInRest = true): PostTypeBuilder
    {
        $this->args['show_in_rest'] = $showInRest;

        return $this;
    }

    /** @deprecated This function does not actually appear to do anything */
    public function position($position = null): PostTypeBuilder
    {
        $this->args['position'] = $position;

        return $this;
    }

    public function capabilities(array $capabilities = []): PostTypeBuilder
    {
        $this->args['capabilities'] = $capabilities;

        return $this;
    }

    public function mapMetaCap(): PostTypeBuilder
    {
        $this->args['map_meta_cap'] = true;

        return $this;
    }

    public function setArgument(string $key, $value): PostTypeBuilder
    {
        $this->args[$key] = $value;

        return $this;
    }

    public function set(): void
    {
        register_post_type($this->postType, $this->args);

        if (!is_null($this->modelClass)) {
            offbeat('post-type')->registerPostModel($this->postType, $this->modelClass);
        }
    }
}
