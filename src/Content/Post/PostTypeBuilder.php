<?php
namespace OffbeatWP\Content\Post;

use Illuminate\Support\Traits\Macroable;

class PostTypeBuilder
{
    use Macroable;

    private $postType     = null;
    private $postTypeArgs = [];
    private $modelClass   = null;

    public function make($postType, $pluralName, $singularLabel)
    {
        $this->postType     = $postType;
        $this->postTypeArgs = [
            'labels' => [
                'name'          => $pluralName,
                'singular_name' => $singularLabel,
            ],
        ];

        return $this;
    }

    public function getPostType()
    {
        return $this->postType;
    }
    
    public function isHierarchical($hierarchical = true)
    {
        $this->postTypeArgs['hierarchical'] = $hierarchical;

        return $this;
    }

    public function rewrite($rewrite)
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

    public function labels($labels)
    {
        if (!isset($this->postTypeArgs['labels'])) {
            $this->postTypeArgs['labels'] = [];
        }

        $this->postTypeArgs['labels'] = array_merge($this->postTypeArgs['labels'], $labels);

        return $this;
    }

    public function model($modelClass)
    {
        $this->modelClass = $modelClass;

        return $this;
    }

    public function supports($support)
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

    public function notPubliclyQueryable()
    {
        $this->postTypeArgs['publicly_queryable'] = false;

        return $this;
    }

    public function public($public = true)
    {
        $this->postTypeArgs['public'] = $public;

        return $this;
    }

    public function excludeFromSearch($exclude = true)
    {
        $this->postTypeArgs['exclude_from_search'] = $exclude;

        return $this;
    }

    public function showUI($showUi = true)
    {
        $this->postTypeArgs['show_ui'] = $showUI;

        return $this;
    }

    public function icon($icon)
    {
        $this->postTypeArgs['menu_icon'] = $icon;

        return $this;
    }

    public function inMenu($menu)
    {
        $this->postTypeArgs['show_in_menu'] = $menu;

        return $this;
    }

    public function taxonomies($taxonomies)
    {
        $this->postTypeArgs['taxonomies'] = $taxonomies;

        return $this;
    }

    public function inRest($showInRest = true)
    {
        $this->postTypeArgs['show_in_rest'] = $showInRest;

        return $this;
    }

    public function position($position = null)
    {
        $this->postTypeArgs['position'] = $position;

        return $this;
    }
    
    public function setArgument($key, $value)
    {
        $this->postTypeArgs[$key] = $value;

        return $this;
    }

    public function capabilities($capabilities = []){
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
