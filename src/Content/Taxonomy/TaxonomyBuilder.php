<?php

namespace OffbeatWP\Content\Taxonomy;

class TaxonomyBuilder
{
    /** @var string|null */
    private $taxonomy = null;
    /** @var array|string|null */
    private $postTypes = null;
    /** @var array|string|null */
    private $args = [];
    /** @var class-string|null */
    private $modelClass = null;

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

    /** @param array|bool $rewrite */
    public function rewrite($rewrite): TaxonomyBuilder
    {
        if (!isset($this->args['rewrite'])) {
            $this->args['rewrite'] = [];
        }

        if ($rewrite === false) {
            $this->args['rewrite'] = false;
        } elseif (is_array($rewrite)) {
            array_push($this->args['rewrite'], $rewrite);
        }

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

    public function hierarchical(bool $hierarchical = false): TaxonomyBuilder
    {
        $this->args['hierarchical'] = $hierarchical;

        return $this;
    }

    public function hierarchyDepth(int $depth): TaxonomyBuilder
    {
        $this->hierarchical($depth);

        add_filter('taxonomy_parent_dropdown_args', function (array $dropdownArgs, string $taxonomy) use ($depth) {
            if ($taxonomy === $this->taxonomy) {
                $dropdownArgs['depth'] = $depth;
            }

            return $dropdownArgs;
        }, 10, 2);

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
    public function showUI(bool $showUi = true): TaxonomyBuilder
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

    /**
     * Used to render a custom metabox
     *
     * __Gutenberg currently does not respect this setting__
     * @param callable $metaBoxCallback
     */
    public function metaBox($metaBoxCallback): TaxonomyBuilder
    {
        $this->args['meta_box_cb'] = $metaBoxCallback;

        return $this;
    }

    /**
     * Used to disable the metabox
     *
     * __Gutenberg currently does not respect this setting__
     */
    public function hideMetaBox(): TaxonomyBuilder
    {
        $this->args['meta_box_cb'] = false;

        return $this;
    }

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

    protected function hideTermDescriptionWrap(): void
    {
        echo '<style> .term-description-wrap { display:none; } </style>';
    }

    /** Hides the "description" field in on the Taxonomy add/edit page */
    public function hideDescriptionField(): TaxonomyBuilder
    {
        add_action($this->taxonomy . '_edit_form', function() { $this->hideTermDescriptionWrap(); });
        add_action($this->taxonomy . '_add_form', function() { $this->hideTermDescriptionWrap(); });

        return $this;
    }

    public function set(): void
    {
        register_taxonomy($this->taxonomy, $this->postTypes, $this->args);

        if (!is_null($this->modelClass)) {
            offbeat('taxonomy')->registerTermModel($this->taxonomy, $this->modelClass);
        }
    }
}
