<?php
namespace OffbeatWP\Wordpress;

class WordpressService
{
    public $bindings = [
        'admin-page' => \OffbeatWP\Support\Wordpress\AdminPage::class,
        'ajax'       => \OffbeatWP\Support\Wordpress\Ajax::class,
        'rest-api'   => \OffbeatWP\Support\Wordpress\RestApi::class,
        'console'    => \OffbeatWP\Support\Wordpress\Console::class,
        'hooks'      => \OffbeatWP\Support\Wordpress\Hooks::class,
        'post-type'  => \OffbeatWP\Support\Wordpress\PostType::class,
        'post'       => \OffbeatWP\Support\Wordpress\Post::class,
        'page'       => \OffbeatWP\Support\Wordpress\Page::class,
        'taxonomy'   => \OffbeatWP\Support\Wordpress\Taxonomy::class,
        'design'     => \OffbeatWP\Support\Wordpress\Design::class,
    ];

    public function register()
    {
        $this->registerMenus();
        $this->registerImageSizes();
        $this->registerSidebars();

        // Page Template
        add_action('init', [$this, 'registerPageTemplate'], 99);
        add_filter('offbeatwp/controller/template', [$this, 'applyPageTemplate'], 10 ,2);
    }

    public function registerMenus()
    {
        $menus = config('menus');

        if (is_object($menus) && $menus->isNotEmpty()) {
            register_nav_menus($menus->toArray());
        }
    }

    public function registerImageSizes()
    {
        $images = config('images');

        if (is_object($images) && $images->isNotEmpty()) {
            $images->each(function ($image, $key) {
                add_image_size($key, $image['width'], $image['height'], $image['crop']);
            });
        }
    }

    public function registerSidebars()
    {
        $sidebars = config('sidebars');

        if (is_object($sidebars) && $sidebars->isNotEmpty()) {
            $sidebars->each(function ($sidebar, $id) {
                $sidebar['id'] = $id;
                register_sidebar($sidebar);
            });
        }
    }

    public function registerPageTemplate()
    {
        add_filter('theme_page_templates', function ($postTemplates) {
            $pageTemplates = offbeat('page')->getPageTemplates();

            if (is_array($pageTemplates)) {
                $postTemplates = array_merge($postTemplates, $pageTemplates);
            }

            return $postTemplates;
        }, 10, 1);
    }

    public function applyPageTemplate($template, $data)
    {
        if (is_singular('page') && empty($data['ignore_page_template'])) {
            $pageTemplate = get_post_meta(get_the_ID(), '_wp_page_template', true);
            if (!empty($pageTemplate) && $pageTemplate != 'default') {
                return $pageTemplate;
            }
        }

        return $template;
    }
}
