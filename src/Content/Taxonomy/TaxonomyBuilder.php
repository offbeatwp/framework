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

    /** @param string[] $labels */
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
