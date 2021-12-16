<?php

namespace OffbeatWP\Content\Post;

use Illuminate\Support\Traits\Macroable;
use WP_Query;

class PostTypeBuilder
{
    use Macroable;

    private $postType = null;
    private $postTypeArgs = [];
    /** @var null|class-string<PostModel>  */
    private $modelClass = null;

    public function make(string $postType, string $pluralLabel, string $singularLabel): PostTypeBuilder
    {
        $this->postType = $postType;
        $this->postTypeArgs = [
            'labels' => [
                'name' => $pluralLabel,
                'singular_name' => $singularLabel,
            ],
        ];

        return $this;
    }

    public function getPostType(): ?string
    {
        return $this->postType;
    }

    public function isHierarchical(bool $hierarchical = true): PostTypeBuilder
    {
        $this->postTypeArgs['hierarchical'] = $hierarchical;

        return $this;
    }

    /** @param string[]|bool[]|int[]|bool $rewrite Valid rewrite array keys include: 'slug', 'with_front', 'hierarchical', 'ep_mask' */
    public function rewrite($rewrite): PostTypeBuilder
    {
        $this->postTypeArgs['rewrite'] = $rewrite;

        return $this;
    }

    /**
     * @param string[] $labels
     * @return $this
     */
    public function labels(array $labels): PostTypeBuilder
    {
        if (!isset($this->postTypeArgs['labels'])) {
            $this->postTypeArgs['labels'] = [];
        }

        $this->postTypeArgs['labels'] = array_merge($this->postTypeArgs['labels'], $labels);

        return $this;
    }

    /**
     * @param class-string<PostModel> $modelClass
     * @return PostTypeBuilder
     */
    public function model(string $modelClass): PostTypeBuilder
    {
        $this->modelClass = $modelClass;

        return $this;
    }

    public function addAdminTableColumn(string $name, string $label, string $modelFunc): PostTypeBuilder
    {
        add_action("manage_{$this->postType}_posts_columns", static function(array $postColumns) use ($label, $name) {
            $postColumns[$name] = $label;
            return $postColumns;
        });

        add_action("manage_{$this->postType}_posts_custom_column", function(string $columnName, int $postId) use ($name, $modelFunc) {
            if ($columnName === $name) {
                $model = new $this->modelClass($postId);
                echo $model->$modelFunc();
            }
        }, 10, 2);

        return $this;
    }

    /**
     * Easily add a sortabke column to the admin table based on a specific meta value
     * @param string $metaName  The meta key. Required.
     * @param string $label     The label to display in the admin column. Displays meta key name if omitted.
     * @param string $orderBy   How the column should be sorted. Defaults to alphatic. Use 'meta_value_num' for numeric sorting.
     * @return $this
     */
    public function addAdminMetaColumn(string $metaName, string $label = '', string $orderBy = 'meta_value'): PostTypeBuilder
    {
        add_action("manage_{$this->postType}_posts_columns", static function(array $postColumns) use ($metaName, $label) {
            $postColumns[$metaName] = $label ?: $metaName;
            return $postColumns;
        });

        add_action("manage_{$this->postType}_posts_custom_column", function(string $columnName, int $postId) use ($metaName) {
            if ($columnName === $metaName) {
                echo get_post_meta($postId, $metaName, true);
            }
        }, 10, 2);

        add_filter("manage_edit-{$this->postType}_sortable_columns", function (array $columns) use ($metaName) {
            $columns[$metaName] = $metaName;
            return $columns;
        });

        add_action('pre_get_posts', function (WP_Query $query) use ($metaName) {
            if (is_admin() && $query->is_main_query() && $query->get('orderby') === $metaName) {
                $query->set('orderby', 'meta_value');
                $query->set('meta_key', $metaName);
            }
        });

        return $this;
    }

    /** @param string[] $supports Valid values: ‘title’ ‘editor’ ‘author’ ‘thumbnail’ ‘excerpt’ ‘trackbacks’ ‘custom-fields’ ‘comments’ ‘revisions’ ‘page-attributes’ ‘post-formats’ */
    public function supports(array $supports): PostTypeBuilder
    {
        $this->postTypeArgs['supports'] = $supports;

        return $this;
    }

    public function notPubliclyQueryable(): PostTypeBuilder
    {
        $this->postTypeArgs['publicly_queryable'] = false;

        return $this;
    }

    public function public(bool $public = true): PostTypeBuilder
    {
        $this->postTypeArgs['public'] = $public;

        return $this;
    }

    public function excludeFromSearch(bool $exclude = true): PostTypeBuilder
    {
        $this->postTypeArgs['exclude_from_search'] = $exclude;

        return $this;
    }

    public function showUI(bool $showUi = true): PostTypeBuilder
    {
        $this->postTypeArgs['show_ui'] = $showUi;

        return $this;
    }

    public function icon(string $icon): PostTypeBuilder
    {
        $this->postTypeArgs['menu_icon'] = $icon;

        return $this;
    }

    /** @param bool|string $menu When true, display as top-level menu. When false, no menu is shown. If a string of an existing top level menu, the post type will be placed as a sub-menu of that. */
    public function inMenu($menu): PostTypeBuilder
    {
        $this->postTypeArgs['show_in_menu'] = $menu;

        return $this;
    }

    public function taxonomies(array $taxonomies): PostTypeBuilder
    {
        $this->postTypeArgs['taxonomies'] = $taxonomies;

        return $this;
    }

    public function inRest(bool $showInRest = true): PostTypeBuilder
    {
        $this->postTypeArgs['show_in_rest'] = $showInRest;

        return $this;
    }

    /** @deprecated This function does not actually appear to do anything */
    public function position($position = null): PostTypeBuilder
    {
        trigger_error('Deprecated position called in PostTypeBuilder.', E_USER_DEPRECATED);
        $this->postTypeArgs['position'] = $position;

        return $this;
    }

    public function capabilityType(string $single, string $plural = ''): PostTypeBuilder
    {
        $this->postTypeArgs['capability_type'] = ($plural) ? [$single, $plural] : $single;

        return $this;
    }

    /** @param string[] $capabilities */
    public function capabilities(array $capabilities = []): PostTypeBuilder
    {
        $this->postTypeArgs['capabilities'] = $capabilities;

        return $this;
    }

    public function mapMetaCap(): PostTypeBuilder
    {
        $this->postTypeArgs['map_meta_cap'] = true;

        return $this;
    }

    public function setArgument(string $key, $value): PostTypeBuilder
    {
        $this->postTypeArgs[$key] = $value;

        return $this;
    }

    public function set(): void
    {
        register_post_type($this->postType, $this->postTypeArgs);

        if (!is_null($this->modelClass)) {
            offbeat('post-type')->registerPostModel($this->postType, $this->modelClass);
        }
    }
}
