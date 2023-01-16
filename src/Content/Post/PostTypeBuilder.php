<?php

namespace OffbeatWP\Content\Post;

use Illuminate\Support\Traits\Macroable;
use WP_Post;
use WP_Query;

class PostTypeBuilder
{
    use Macroable;

    /** @var null|class-string<PostModel> */
    private ?string $modelClass = null;
    private ?string $postType = null;
    private array $postTypeArgs = [];

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
     * <b>name</b> – General name for the post type, usually plural. The same and overridden by $post_type_object->label. Default is ‘Posts’ / ‘Pages’.<br>
     * <b>singular_name</b> – Name for one object of this post type. Default is ‘Post’ / ‘Page’.<br>
     * <b>add_new</b> – Default is ‘Add New’.<br>
     * <b>add_new_item</b> – Label for adding a new singular item. Default is ‘Add New Post’.<br>
     * <b>edit_item</b> – Label for editing a singular item. Default is ‘Edit Post’.<br>
     * <b>new_item</b> – Label for the new item page title. Default is ‘New Post’.<br>
     * <b>view_item</b> – Label for viewing a singular item. Default is ‘View Post’.<br>
     * <b>view_items</b> – Label for viewing post type archives. Default is ‘View Posts’.<br>
     * <b>search_items</b> – Label for searching plural items. Default is ‘Search Posts’.<br>
     * <b>not_found</b> – Label used when no items are found. Default is ‘No posts found’.<br>
     * <b>not_found_in_trash</b> – Label used when no items are in the Trash. Default is ‘No posts found in Trash’.<br>
     * <b>parent_item_colon</b> – Label used to prefix parents of hierarchical items. Not used on non-hierarchical post types. Default is ‘Parent Page:’.<br>
     * <b>all_items</b> – Label to signify all items in a submenu link. Default is ‘All Posts’.<br>
     * <b>archives</b> – Label for archives in nav menus. Default is ‘Post Archives’.<br>
     * <b>attributes</b> – Label for the attributes meta box. Default is ‘Post Attributes’.<br>
     * <b>insert_into_item</b> – Label for the media frame button. Default is ‘Insert into post’.<br>
     * <b>uploaded_to_this_item</b> – Label for the media frame filter. Default is ‘Uploaded to this post’.<br>
     * <b>featured_image</b> – Label for the featured image meta box title. Default is ‘Featured image’.<br>
     * <b>set_featured_image</b> – Label for setting the featured image. Default is ‘Set featured image’.<br>
     * <b>remove_featured_image</b> – Label for removing the featured image. Default is ‘Remove featured image’.<br>
     * <b>use_featured_image</b> – Label in the media frame for using a featured image. Default is ‘Use as featured image’.<br>
     * <b>menu_name</b> – Label for the menu name. Default is the same as name.<br>
     * <b>filter_items_list</b> – Label for the table views hidden heading. Default is ‘Filter posts list’.<br>
     * <b>filter_by_date</b> – Label for the date filter in list tables. Default is ‘Filter by date’.<br>
     * <b>items_list_navigation</b> – Label for the table pagination hidden heading. Default is ‘Posts list navigation’.<br>
     * <b>items_list</b> – Label for the table hidden heading. Default is ‘Posts list’.<br>
     * <b>item_published</b> – Label used when an item is published. Default is ‘Post published.’ / ‘Page published.’<br>
     * <b>item_published_privately</b> – Label used when an item is published with private visibility. Default is ‘Post published privately.’<br>
     * <b>item_reverted_to_draft</b> – Label used when an item is switched to a draft. Default is ‘Post reverted to draft.’<br>
     * <b>item_scheduled</b> – Label used when an item is scheduled for publishing. Default is ‘Post scheduled.’<br>
     * <b>item_updated</b> – Label used when an item is updated. Default is ‘Post updated.’<br>
     * <b>item_link</b> – Title for a navigation link block variation. Default is ‘Post Link’.<br>
     * <b>item_link_description</b> – Description for a navigation link block variation. Default is ‘A link to a post.’<br>
     * <b>enter_title_here</b> - Post title placeholder text. Default is 'Add title'.
     *
     * @param array{name?: string, singular_name?: string, add_new?: string, add_new_item?: string, edit_item?: string, new_item?: string, view_item?: string, view_items?: string, search_items?: string, not_found?: string, not_found_in_trash?: string, parent_item_colon?: string, all_items?: string, archives?: string, attributes?: string, insert_into_item?: string, uploaded_to_this_item?: string, featured_image?: string, set_featured_image?: string, remove_featured_image?: string, use_featured_image?: string, menu_name?: string, filter_items_list?: string, filter_by_date?: string, items_list_navigation?: string, items_list?: string, item_published?: string, item_published_privately?: string, item_reverted_to_draft?: string, item_scheduled?: string, item_updated?: string, item_link?: string, item_link_description?: string, enter_title_here?: string} $labels An array of labels for this post type.
     * @return $this
     */
    public function labels(array $labels): PostTypeBuilder
    {
        if (!isset($this->postTypeArgs['labels'])) {
            $this->postTypeArgs['labels'] = [];
        }

        // WP requires the use of a filter to add unique title placeholder text....
        if (isset($labels['enter_title_here'])) {
            add_filter('enter_title_here', function ($text, WP_Post $post) use ($labels) {
                return ($post->post_type === $this->getPostType()) ? $labels['enter_title_here'] : $text;
            }, 10, 2);
            unset($labels['enter_title_here']);
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

    /**
     * Adds a new filter dropdown to the admin table.
     * @param string $metaKey The metakey that should be filtered on.
     * @param iterable $choices An array of choices to choose from, keyed by their meta value. Falsy values will be treated as an 'all' option.
     * @return $this
     */
    public function addAdminTableFilter(string $metaKey, iterable $choices): PostTypeBuilder
    {
        add_action('restrict_manage_posts', function () use ($metaKey, $choices) {
            $screen = get_current_screen();
            if ($screen && $screen->base === 'edit' && $screen->post_type === $this->postType) {
                $selected = filter_input(INPUT_GET, $metaKey, FILTER_SANITIZE_STRING);

                echo '<select name="' . $metaKey . '">';
                foreach ($choices as $key => $value) {
                    echo '<option value="' . $key . '" ' . (($selected === $key) ? 'selected="selected"' : '') . '>' . $value . '</option>';
                }
                echo '</select>';
            }
        });

        add_action('pre_get_posts', function (WP_Query $query) use ($metaKey) {
            if (is_admin() && $query->is_main_query()) {
                $screen = get_current_screen();

                if ($screen && $screen->base === 'edit' && $screen->post_type === $this->postType && !empty($_GET[$metaKey])) {
                    $query->set('meta_query', [['key' => $metaKey, 'value' => $_GET[$metaKey]]]);
                }
            }
        });

        return $this;
    }

    /**
     * @param string $name
     * @param string $label
     * @param non-empty-string|callable $modelFunc
     * @param string $metaKeyForSorting
     * @return $this
     */
    public function addAdminTableColumn(string $name, string $label, $modelFunc, string $metaKeyForSorting = ''): PostTypeBuilder
    {
        add_filter("manage_{$this->postType}_posts_columns", static function (array $postColumns) use ($label, $name) {
            $postColumns[$name] = $label;
            return $postColumns;
        });

        add_action("manage_{$this->postType}_posts_custom_column", function (string $columnName, int $postId) use ($name, $modelFunc) {
            if ($columnName === $name) {
                $model = new $this->modelClass($postId);

                if (is_string($modelFunc)) {
                    echo $model->$modelFunc();
                } else {
                    echo $modelFunc($model);
                }
            }
        }, 10, 2);

        if ($metaKeyForSorting) {
            add_filter("manage_edit-{$this->postType}_sortable_columns", function (array $columns) use ($name, $metaKeyForSorting) {
                $columns[$name] = $metaKeyForSorting;
                return $columns;
            });

            add_action('pre_get_posts', function (WP_Query $query) use ($name, $metaKeyForSorting) {
                if (is_admin() && $query->is_main_query() && $query->get('orderby') === $name) {
                    $query->set('orderby', 'meta_value');
                    $query->set('meta_key', $metaKeyForSorting);
                }
            });
        }

        return $this;
    }

    /**
     * Easily add a sortable column to the admin table based on a specific meta value.
     * @param string $metaName The meta key. Required.
     * @param string $label The label to display in the admin column. Displays meta key name if omitted.
     * @param string $orderBy How the column should be sorted. Defaults to 'meta_value' which is alphabetic. <br>Use 'meta_value_num' for numeric sorting.
     * @param null|callable $callback Optional. Provide a callback to modify the data before it is rendered. <br><b>The sorting will still happen based on the original meta value.</b>
     * @return $this
     */
    public function addAdminMetaColumn(string $metaName, string $label = '', string $orderBy = 'meta_value', ?callable $callback = null): PostTypeBuilder
    {
        add_filter("manage_{$this->postType}_posts_columns", static function (array $postColumns) use ($metaName, $label) {
            $postColumns[$metaName] = $label ?: $metaName;
            return $postColumns;
        });

        add_action("manage_{$this->postType}_posts_custom_column", function (string $columnName, int $postId) use ($metaName, $callback) {
            if ($columnName === $metaName) {
                $metaValue = get_post_meta($postId, $metaName, true);

                if ($callback) {
                    $model = new $this->modelClass($postId);
                    echo $callback($model, $metaValue);
                } else {
                    echo $metaValue;
                }
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

    public function setAdminTableColumnLabel(string $name, string $newLabel): PostTypeBuilder
    {
        add_filter("manage_{$this->postType}_posts_columns", static function (array $columns) use ($name, $newLabel) {
            $columns[$name] = $newLabel;
            return $columns;
        });

        return $this;
    }

    public function removeAdminTableColumn(string $name): PostTypeBuilder
    {
        add_filter("manage_{$this->postType}_posts_columns", static function (array $columns) use ($name) {
            unset($columns[$name]);
            return $columns;
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

    public function mapMetaCap(bool $mapMetaCap = true): PostTypeBuilder
    {
        $this->postTypeArgs['map_meta_cap'] = $mapMetaCap;

        return $this;
    }

    /**
     * @param non-empty-string $singleName Must be CamelCase.
     * @param string $pluralName Must be CamelCase. Defaults to singlename if omitted.
     */
    public function showInGraphQl(string $singleName, string $pluralName = ''): PostTypeBuilder
    {
        $this->postTypeArgs['show_in_graphql'] = true;
        $this->postTypeArgs['graphql_single_name'] = $singleName;
        $this->postTypeArgs['graphql_plural_name'] = $pluralName ?: $singleName;

        return $this;
    }

    /**
     * @param string $key
     * @param scalar|array $value
     * @return PostTypeBuilder
     */
    public function setArgument(string $key, $value): PostTypeBuilder
    {
        $this->postTypeArgs[$key] = $value;

        return $this;
    }

    public function set(): void
    {
        register_post_type($this->postType, $this->postTypeArgs);

        if ($this->modelClass !== null) {
            offbeat('post-type')->registerPostModel($this->postType, $this->modelClass);
        }
    }
}
