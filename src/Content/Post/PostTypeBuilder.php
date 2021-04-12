<?php

namespace OffbeatWP\Content\Post;

use Illuminate\Support\Traits\Macroable;

class PostTypeBuilder
{
    use Macroable;

    private $postType = null;
    private $postTypeArgs = [];
    private $modelClass = null;

    public function make($postType, $pluralName, $singularLabel): PostTypeBuilder
    {
        $this->postType = $postType;
        $this->postTypeArgs = [
            'labels' => [
                'name' => $pluralName,
                'singular_name' => $singularLabel,
            ],
        ];

        return $this;
    }

    public function getPostType()
    {
        return $this->postType;
    }

    public function isHierarchical($hierarchical = true): PostTypeBuilder
    {
        $this->postTypeArgs['hierarchical'] = $hierarchical;

        return $this;
    }

    public function rewrite($rewrite): PostTypeBuilder
    {
        if (!isset($this->postTypeArgs['rewrite'])) {
            $this->postTypeArgs['rewrite'] = [];
        }

        if ($rewrite === false) {
            $this->postTypeArgs['rewrite'] = false;
        } elseif (is_array($rewrite)) {
            $this->postTypeArgs['rewrite'] = array_merge($this->postTypeArgs['rewrite'], $rewrite);
        }

        return $this;
    }

    public function labels($labels): PostTypeBuilder
    {
        if (!isset($this->postTypeArgs['labels'])) {
            $this->postTypeArgs['labels'] = [];
        }

        $this->postTypeArgs['labels'] = array_merge($this->postTypeArgs['labels'], $labels);

        return $this;
    }

    /**
     * Attempt to automatically generate labels
     * BEWARE: This will only work properly if the passed singular/plural words are UNLOCALISED and lowercase
     */
    public function generateLabels(string $domain = 'offbeatwp'): PostTypeBuilder {
        if (!isset($this->postTypeArgs['labels'])) {
            $this->postTypeArgs['labels'] = [];
        }

        $singular = $this->postTypeArgs['labels']['singular_name'];
        $plural = $this->postTypeArgs['labels']['name'];
        $ucf_singular = ucfirst($singular);
        $ucf_plural = ucfirst($plural);

        array_merge([
            'name'                     => _x($plural, "post type general name", $domain),
            'singular_name'            => _x($singular, "post type singular name", $domain),
            'add_new_item'             => __("Add New {$ucf_singular}", $domain),
            'edit_item'                => __("Edit {$ucf_singular}", $domain),
            'new_item'                 => __("New {$ucf_singular}", $domain),
            'view_item'                => __("View {$ucf_singular}", $domain),
            'view_items'               => __("View {$ucf_plural}", $domain),
            'search_items'             => __("Search {$ucf_plural}", $domain),
            'not_found'                => __("No {$plural} found.", $domain),
            'not_found_in_trash'       => __("No {$plural} found in Trash.", $domain),
            'all_items'                => __("All {$ucf_plural}", $domain),
            'archives'                 => __("{$ucf_singular} Archives", $domain),
            'attributes'               => __("{$ucf_singular} Attributes", $domain),
            'insert_into_item'         => __( "Insert into {$singular}", $domain),
            'uploaded_to_this_item'    => __("Uploaded to this {$singular}", $domain),
            'filter_items_list'        => __("Filter {$plural} list", $domain),
            'items_list_navigation'    => __("{$ucf_plural} list navigation", $domain),
            'items_list'               => __("{$ucf_plural} list", $domain),
            'item_published'           => __("{$ucf_singular} published.", $domain),
            'item_published_privately' => __("{$ucf_singular} published privately.", $domain),
            'item_reverted_to_draft'   => __("{$ucf_singular} reverted to draft.", $domain),
            'item_scheduled'           => __("{$ucf_singular} scheduled.", $domain),
            'item_updated'             => __("{$ucf_singular} updated.", $domain),
        ], $this->postTypeArgs['labels']);

        return $this;
    }

    public function model($modelClass): PostTypeBuilder
    {
        $this->modelClass = $modelClass;

        return $this;
    }

    public function supports($support): PostTypeBuilder
    {
        if (!isset($this->postTypeArgs['supports'])) {
            $this->postTypeArgs['supports'] = [];
        }

        if (is_array($support)) {
            $this->postTypeArgs['supports'] = $this->postTypeArgs['supports'] + $support;
        } else {
            array_push($this->postTypeArgs['supports'], $support);
        }

        return $this;
    }

    public function notPubliclyQueryable(): PostTypeBuilder
    {
        $this->postTypeArgs['publicly_queryable'] = false;

        return $this;
    }

    public function public($public = true): PostTypeBuilder
    {
        $this->postTypeArgs['public'] = $public;

        return $this;
    }

    public function excludeFromSearch($exclude = true): PostTypeBuilder
    {
        $this->postTypeArgs['exclude_from_search'] = $exclude;

        return $this;
    }

    public function showUI($showUi = true): PostTypeBuilder
    {
        $this->postTypeArgs['show_ui'] = $showUi;

        return $this;
    }

    public function icon($icon): PostTypeBuilder
    {
        $this->postTypeArgs['menu_icon'] = $icon;

        return $this;
    }

    public function inMenu($menu): PostTypeBuilder
    {
        $this->postTypeArgs['show_in_menu'] = $menu;

        return $this;
    }

    public function taxonomies($taxonomies): PostTypeBuilder
    {
        $this->postTypeArgs['taxonomies'] = $taxonomies;

        return $this;
    }

    public function inRest($showInRest = true): PostTypeBuilder
    {
        $this->postTypeArgs['show_in_rest'] = $showInRest;

        return $this;
    }

    public function position($position = null): PostTypeBuilder
    {
        $this->postTypeArgs['position'] = $position;

        return $this;
    }

    public function setArgument($key, $value): PostTypeBuilder
    {
        $this->postTypeArgs[$key] = $value;

        return $this;
    }

    public function capabilities($capabilities = []): PostTypeBuilder
    {
        $this->postTypeArgs['capabilities'] = $capabilities;

        return $this;
    }

    public function set()
    {
        register_post_type($this->postType, $this->postTypeArgs);

        if (!is_null($this->modelClass)) {
            offbeat('post-type')->registerPostModel($this->postType, $this->modelClass);
        }
    }
}
