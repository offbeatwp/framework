<?php

namespace OffbeatWP\Content\Taxonomy;

class TaxonomyBuilder
{
    /** @var string|null $taxonomy */
    private $taxonomy = null;
    private $postTypes = null;
    private $args = [];
    private $modelClass = null;

    public function make($taxonomy, $postTypes, $pluralName, $singularLabel): TaxonomyBuilder
    {
        $this->taxonomy = $taxonomy;
        $this->postTypes = $postTypes;
        $this->args = [
            'labels' => [
                'name' => $pluralName,
                'singular_name' => $singularLabel,
            ],
        ];

        return $this;
    }

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

    public function labels($labels): TaxonomyBuilder
    {
        if (!isset($this->args['labels'])) {
            $this->args['labels'] = [];
        }

        $this->args['labels'] = array_merge($this->args['labels'], $labels);

        return $this;
    }

    public function hierarchical($hierarchical = false): TaxonomyBuilder
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

    public function public($public = true): TaxonomyBuilder
    {
        $this->args['public'] = $public;

        return $this;
    }

    public function showUI($showUi = true): TaxonomyBuilder
    {
        $this->args['show_ui'] = $showUi;

        return $this;
    }

    public function inMenu($menu): TaxonomyBuilder
    {
        $this->args['show_in_menu'] = $menu;

        return $this;
    }

    public function inRest($rest = true): TaxonomyBuilder
    {
        $this->args['show_in_rest'] = $rest;

        return $this;
    }

    public function showAdminColumn($showAdminColumn = true): TaxonomyBuilder
    {
        $this->args['show_admin_column'] = $showAdminColumn;

        return $this;
    }

    public function metaBox($metaBoxCallback): TaxonomyBuilder
    {
        $this->args['meta_box_cb'] = $metaBoxCallback;

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

    protected function hideTermDescriptionWrap(): void {
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
