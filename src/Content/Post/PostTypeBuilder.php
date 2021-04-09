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
    public function generateLabels(string $domain) {
        $singular = $this->postTypeArgs['labels']['singular_name'];
        $plural = $this->postTypeArgs['labels']['name'];

        $this->postTypeArgs['labels'] = [
            'name'                     => _x( $plural, "post type general name", $domain),
            'singular_name'            => _x( $singular, "post type singular name", $domain),
            'add_new'                  => _x( "Add New", strtolower($singular), $domain),
            'add_new_item'             => __( "Add New {$singular}" ),
            'edit_item'                => __( 'Edit Post' ),
            'new_item'                 => __( 'New Post'),
            'view_item'                => __( 'View Post'),
            'view_items'               => __( 'View Posts'),
            'search_items'             => __( 'Search Posts'),
            'not_found'                => __( 'No posts found.'),
            'not_found_in_trash'       => __( 'No posts found in Trash.'),
            'parent_item_colon'        => __( 'Parent Page:'),
            'all_items'                => __( 'All Posts'),
            'archives'                 => __( 'Post Archives'),
            'attributes'               => __( 'Post Attributes'),
            'insert_into_item'         => __( "Insert into post" ),
            'uploaded_to_this_item'    => __( 'Uploaded to this post'),
            'featured_image'           => _x( 'Featured image', 'post' ),
            'set_featured_image'       => _x( 'Set featured image', 'post'),
            'remove_featured_image'    => _x( 'Remove featured image', 'post'),
            'use_featured_image'       => _x( 'Use as featured image', 'post'),
            'filter_items_list'        => __( 'Filter posts list'),
            'filter_by_date'           => __( 'Filter by date'),
            'items_list_navigation'    => __( 'Posts list navigation'),
            'items_list'               => __( 'Posts list'),
            'item_published'           => __( 'Post published.'),
            'item_published_privately' => __( 'Post published privately.'),
            'item_reverted_to_draft'   => __( 'Post reverted to draft.'),
            'item_scheduled'           => __( 'Post scheduled.'),
            'item_updated'             => __( 'Post updated.'),
        ];
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
