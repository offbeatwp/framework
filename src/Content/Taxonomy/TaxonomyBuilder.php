<?php

namespace OffbeatWP\Content\Taxonomy;

class TaxonomyBuilder
{
    /** @var non-empty-string|null */
    private $taxonomy = null;
    /** @var non-empty-string[]|string|null */
    private $postTypes = null;
    /** @var array */
    private $args = [];
    /** @var class-string|null */
    private $modelClass = null;

    /**
     * @param non-empty-string $taxonomy
     * @param non-empty-string|non-empty-string[] $postTypes
     * @param non-empty-string $pluralName
     * @param non-empty-string $singularLabel
     * @return $this
     */
    public function make($taxonomy, $postTypes, $pluralName, $singularLabel): TaxonomyBuilder
    {
        $this->taxonomy = $taxonomy;
        $this->postTypes = $postTypes;
        $this->args = [
            'labels' => ['name' => $pluralName, 'singular_name' => $singularLabel],
        ];

        return $this;
    }

    /** @param string[] $capabilities Valid keys include: 'manage_terms', 'edit_terms', 'delete_terms' and 'assign_terms' */
    public function capabilities(array $capabilities = []): TaxonomyBuilder
    {
        $this->args['capabilities'] = $capabilities;

        return $this;
    }

    /** @param string[]|bool[]|int[]|bool $rewrite Valid rewrite array keys include: 'slug', 'with_front', 'hierarchical', 'ep_mask' */
    public function rewrite($rewrite): TaxonomyBuilder
    {
        $this->args['rewrite'] = $rewrite;

        return $this;
    }

    /**
     * <b>name</b> – General name for the taxonomy, usually plural. The same as and overridden by $tax->label. Default 'Tags'/'Categories'.<br>
     * <b>singular_name</b> – Name for one object of this taxonomy. Default 'Tag'/'Category'.<br>
     * <b>search_items</b> – Default 'Search Tags'/'Search Categories'.<br>
     * <b>popular_items</b> – This label is only used for non-hierarchical taxonomies. Default 'Popular Tags'.<br>
     * <b>all_items</b> –  Default 'All Tags'/'All Categories'.<br>
     * <b>parent_item</b> – This label is only used for hierarchical taxonomies. Default 'Parent Category'.<br>
     * <b>parent_item_colon</b> – The same as parent_item, but with colon : in the end.<br>
     * <b>name_field_description</b> – Description for the Name field on Edit Tags screen. Default 'The name is how it appears on your site'.<br>
     * <b>slug_field_description</b> – Description for the Slug field on Edit Tags screen. Default 'The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens'.<br>
     * <b>parent_field_description</b> – Description for the Parent field on Edit Tags screen. Default 'Assign a parent term to create a hierarchy.The term Jazz, for example, would be the parent of Bebop and Big Band'.<br>
     * <b>desc_field_description</b> – Description for the Description field on Edit Tags screen. Default 'The description is not prominent by default; however, some themes may show it'.<br>
     * <b>edit_item</b> – Default 'Edit Tag'/'Edit Category'.<br>
     * <b>view_item</b> – Default 'View Tag'/'View Category'.<br>
     * <b>update_item</b> – Default 'Update Tag'/'Update Category'.<br>
     * <b>add_new_item</b> – Default 'Add New Tag'/'Add New Category'.<br>
     * <b>new_item_name</b> – Default 'New Tag Name'/'New Category Name'.<br>
     * <b>separate_items_with_commas</b> – This label is only used for non-hierarchical taxonomies. Default 'Separate tags with commas', used in the meta box.<br>
     * <b>add_or_remove_items</b> – This label is only used for non-hierarchical taxonomies. Default 'Add or remove tags', used in the meta box when JavaScript is disabled.<br>
     * <b>choose_from_most_used</b> – This label is only used on non-hierarchical taxonomies. Default 'Choose from the most used tags', used in the meta box.<br>
     * <b>not_found</b> – Default 'No tags found'/'No categories found', used in the meta box and taxonomy list table.<br>
     * <b>no_terms</b> – Default 'No tags'/'No categories', used in the posts and media list tables.<br>
     * <b>filter_by_item</b> – This label is only used for hierarchical taxonomies. Default 'Filter by category', used in the posts list table.<br>
     * <b>items_list_navigation</b> – Label for the table pagination hidden heading.<br>
     * <b>items_list</b> – Label for the table hidden heading.<br>
     * <b>most_used</b> – Title for the Most Used tab. Default 'Most Used'.<br>
     * <b>back_to_items</b> – Label displayed after a term has been updated.<br>
     * <b>item_link</b> – Used in the block editor. Title for a navigation link block variation.Default 'Tag Link'/'Category Link'.<br>
     * <b>item_link_description</b> – Used in the block editor. Description for a navigation link block variation. Default 'A link to a tag'/'A link to a category'.<br>
     * @param array{name: string, singular_name: string, search_items: string, popular_items: string, all_items: string, parent_item: string, parent_item_colon: string, name_field_description: string, slug_field_description: string, parent_field_description: string, desc_field_description: string, edit_item: string, view_item: string, update_item: string, add_new_item: string, new_item_name: string, separate_items_with_commas: string, add_or_remove_items: string, choose_from_most_used: string, not_found: string, no_terms: string, filter_by_item: string, items_list_navigation: string, items_list: string, most_used: string, back_to_items: string, item_link: string, item_link_description: string} $labels
     * @return TaxonomyBuilder
     */
    public function labels(array $labels): TaxonomyBuilder
    {
        if (!isset($this->args['labels'])) {
            $this->args['labels'] = [];
        }

        $this->args['labels'] = array_merge($this->args['labels'], $labels);

        return $this;
    }

    public function hierarchyDepth(int $depth): TaxonomyBuilder
    {
        $this->hierarchical((bool)$depth);

        add_filter('taxonomy_parent_dropdown_args', function (array $dropdownArgs, string $taxonomy) use ($depth) {
            if ($taxonomy === $this->taxonomy) {
                $dropdownArgs['depth'] = $depth;
            }

            return $dropdownArgs;
        }, 10, 2);

        return $this;
    }

    public function hierarchical(bool $hierarchical = false): TaxonomyBuilder
    {
        $this->args['hierarchical'] = $hierarchical;

        return $this;
    }

    public function model($modelClass): TaxonomyBuilder
    {
        $this->modelClass = $modelClass;

        return $this;
    }

    public function notPubliclyQueryable(): TaxonomyBuilder
    {
        $this->args['publicly_queryable'] = false;

        return $this;
    }

    /** Whether a taxonomy is intended for use publicly either via the admin interface or by front-end users */
    public function public(bool $public = true): TaxonomyBuilder
    {
        $this->args['public'] = $public;

        return $this;
    }

    /** Whether to generate and allow a UI for managing terms in this taxonomy in the admin */
    public function showUi(bool $showUi = true): TaxonomyBuilder
    {
        $this->args['show_ui'] = $showUi;

        return $this;
    }

    /** Makes this taxonomy available for selection in navigation menus */
    public function showNavMenus(bool $show = true): TaxonomyBuilder
    {
        $this->args['show_in_nav_menus'] = $show;

        return $this;
    }

    /** Whether to list the taxonomy in the Tag Cloud Widget controls */
    public function showTagCloud(bool $show = true): TaxonomyBuilder
    {
        $this->args['show_tagcloud'] = $show;

        return $this;
    }

    /** Whether this taxonomy should be shown in the admin menu */
    public function inMenu(bool $menu): TaxonomyBuilder
    {
        $this->args['show_in_menu'] = $menu;

        return $this;
    }

    /** Whether to include the taxonomy in the REST API */
    public function inRest(bool $rest = true): TaxonomyBuilder
    {
        $this->args['show_in_rest'] = $rest;

        return $this;
    }

    /** Whether to display a column for the taxonomy on its post type listing screens */
    public function showAdminColumn(bool $showAdminColumn = true): TaxonomyBuilder
    {
        $this->args['show_admin_column'] = $showAdminColumn;

        return $this;
    }

    /** Used to disable the metabox */
    public function hideMetaBox(): TaxonomyBuilder
    {
        $this->args['meta_box_cb'] = false;

        return $this;
    }

    /** @deprecated Does not work with Gutenberg editor. */
    public function useCheckboxes(): TaxonomyBuilder
    {
        $this->metaBox('post_categories_meta_box');

        add_filter('post_edit_category_parent_dropdown_args', function ($args) {
            if ($args['taxonomy'] === $this->taxonomy) {
                $args['echo'] = false;
            }

            return $args;
        });

        return $this;
    }

    public function addAdminMetaColumn(string $metaName, string $label = '', string $orderBy = 'meta_value', ?callable $callback = null): TaxonomyBuilder
    {
        add_filter("manage_edit-{$this->taxonomy}_columns", function ($columns) use ($metaName, $label) {
            $columns[$metaName] = $label ?: $metaName;
            return $columns;
        });

        add_filter("manage_{$this->taxonomy}_custom_column", function ($content, $columnName, $termId) use ($metaName, $callback) {
            if ($columnName === $metaName) {
                $content = get_term_meta($termId, $metaName, true);

                if ($callback) {
                    $content = $callback(new $this->modelClass($termId), $content);
                }
            }

            return $content;
        }, 10, 3);

        return $this;
    }

    /**
     * Used to render a custom metabox
     *
     * @param callable $metaBoxCallback
     * @deprecated Gutenberg does not respect this setting and the devs indicated that they don't care
     */
    public function metaBox($metaBoxCallback): TaxonomyBuilder
    {
        $this->args['meta_box_cb'] = $metaBoxCallback;

        return $this;
    }

    /** Hides the "description" field in on the Taxonomy add/edit page */
    public function hideDescriptionField(): TaxonomyBuilder
    {
        add_action($this->taxonomy . '_edit_form', function () {
            $this->_hideTermDescriptionWrap();
        });
        add_action($this->taxonomy . '_add_form', function () {
            $this->_hideTermDescriptionWrap();
        });

        return $this;
    }

    private function _hideTermDescriptionWrap(): void
    {
        echo '<style>.term-description-wrap, th.column-description, td.column-description { display:none; }</style>';
    }

    /**
     * Hides the parent field on the add/edit taxonomy pages.<br/>
     * <b>Note:</b> This will also hide the "add new term" option on all post edit/add pages.
     */
    public function hideParentField(): TaxonomyBuilder
    {
        add_action($this->taxonomy . '_edit_form', function () {
            $this->_hideTermParentWrap();
        });
        add_action($this->taxonomy . '_add_form', function () {
            $this->_hideTermParentWrap();
        });
        add_action('admin_footer-post.php', function () {
            $this->_hideTermAddWrap($this->taxonomy);
        });
        add_action('admin_footer-post-new.php', function () {
            $this->_hideTermAddWrap($this->taxonomy);
        });

        return $this;
    }

    /**
     * @param non-empty-string $singleName Must be CamelCase.
     * @param string $pluralName Must be CamelCase. Defaults to singlename if omitted.
     */
    public function showInGraphQl(string $singleName, string $pluralName = ''): TaxonomyBuilder
    {
        $this->args['show_in_graphql'] = true;
        $this->args['graphql_single_name'] = $singleName;
        $this->args['graphql_plural_name'] = $pluralName ?: $singleName;

        return $this;
    }

    private function _hideTermParentWrap(): void
    {
        echo '<style> .term-parent-wrap { display:none; } </style>';
    }

    private function _hideTermAddWrap(?string $taxonomy): void
    {
        if ($taxonomy) {
            $targetElm = "#{$taxonomy}-adder";
            echo '<style>' . $targetElm . ' { display:none; } </style>';
        }
    }

    public function set(): void
    {
        register_taxonomy($this->taxonomy, $this->postTypes, $this->args);

        if ($this->modelClass !== null) {
            offbeat('taxonomy')->registerTermModel($this->taxonomy, $this->modelClass);
        }
    }
}
