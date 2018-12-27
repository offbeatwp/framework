<?php

namespace OffbeatWP\Views;

class PostTemplate
{
    public $label;
    public $name;
    public $postType;

    public function __construct($label, $name, $postType = 'page', $customView = true)
    {
        $this->label = $label;
        $this->name = $name;
        $this->postType = $postType;

        add_filter('theme_' . $postType . '_templates', [$this, 'register'], 10, 4);

        if ($customView) {
            add_filter('controller_view', [$this, 'customControllerView']);
        }
    }

    public function register($post_templates, $wp_theme, $post, $post_type)
    {
        $post_templates[$this->name] = $this->label;

        return $post_templates;
    }

    public function customControllerView($template)
    {
        if (is_singular($this->postType)) {
            $page_template = get_post_meta(get_the_ID(), '_wp_page_template', true);

            if ($page_template === $this->name) {
                return 'layouts/page_' . $this->name;
            }
        }

        return $template;
    }
}