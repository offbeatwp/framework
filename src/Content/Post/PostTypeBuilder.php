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

    public function getPostType(): ?string
    {
        return $this->postType;
    }

    public function isHierarchical(bool $hierarchical = true): PostTypeBuilder
    {
        $this->postTypeArgs['hierarchical'] = $hierarchical;

        return $this;
    }

    /** @param bool|array $rewrite Triggers the handling of rewrites for this post type. To prevent rewrites, set to false. */
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

    public function labels(array $labels): PostTypeBuilder
    {
        if (!isset($this->postTypeArgs['labels'])) {
            $this->postTypeArgs['labels'] = [];
        }

        $this->postTypeArgs['labels'] = array_merge($this->postTypeArgs['labels'], $labels);

        return $this;
    }

    public function model(string $modelClass): PostTypeBuilder
    {
        $this->modelClass = $modelClass;

        return $this;
    }

    /** @param string[]|false $support Valid values: ‘title’ ‘editor’ ‘author’ ‘thumbnail’ ‘excerpt’ ‘trackbacks’ ‘custom-fields’ ‘comments’ ‘revisions’ ‘page-attributes’ ‘post-formats’ */
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

    /** @param bool|string $menu If false, no menu is shown. If a string of an existing top level menu, the post type will be placed as a sub-menu of that. */
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

    public function position($position = null): PostTypeBuilder
    {
        $this->postTypeArgs['position'] = $position;

        return $this;
    }

    public function capabilities(array $capabilities = []): PostTypeBuilder
    {
        $this->postTypeArgs['capabilities'] = $capabilities;

        return $this;
    }

    public function setArgument(string $key, $value): PostTypeBuilder
    {
        $this->postTypeArgs[$key] = $value;

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
