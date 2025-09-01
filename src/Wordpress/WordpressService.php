<?php

namespace OffbeatWP\Wordpress;

use OffbeatWP\Services\AbstractService;
use OffbeatWP\Support\Wordpress\AdminPage;
use OffbeatWP\Support\Wordpress\Ajax;
use OffbeatWP\Support\Wordpress\Console;
use OffbeatWP\Support\Wordpress\Design;
use OffbeatWP\Support\Wordpress\Page;
use OffbeatWP\Support\Wordpress\Post;
use OffbeatWP\Support\Wordpress\PostType;
use OffbeatWP\Support\Wordpress\RestApi;
use OffbeatWP\Support\Wordpress\Taxonomy;

final class WordpressService extends AbstractService
{
    /** @var class-string[] */
    public array $bindings = [
        'admin-page' => AdminPage::class,
        'ajax'       => Ajax::class,
        'rest-api'   => RestApi::class,
        'console'    => Console::class,
        'post-type'  => PostType::class,
        'post'       => Post::class,
        'page'       => Page::class,
        'taxonomy'   => Taxonomy::class,
        'design'     => Design::class
    ];

    public function register(): void
    {
        add_action('after_setup_theme', [$this, 'registerMenus']);
        $this->registerImageSizes();
        $this->registerSidebars();

        // Page Template
        add_action('init', [$this, 'registerPageTemplate'], 99);
        add_filter('offbeatwp/controller/template', [$this, 'applyPageTemplate'], 10, 2);
    }

    public function registerMenus(): void
    {
        $menus = config('menus', false);

        if (is_array($menus) && $menus) {
            register_nav_menus($menus);
        }
    }

    public function registerImageSizes(): void
    {
        $images = config('images', false);

        if (is_array($images) && $images) {
            foreach ($images as $key => $image) {
                add_image_size($key, $image['width'], $image['height'], $image['crop']);
            }
        }
    }

    public function registerSidebars(): void
    {
        $sidebars = config('sidebars', false);

        if (is_array($sidebars) && $sidebars) {
            foreach ($sidebars as $id => $sidebar) {
                $sidebar['id'] = $id;
                register_sidebar($sidebar);
            }
        }
    }

    public function registerPageTemplate(): void
    {
        add_filter('theme_page_templates', function ($postTemplates) {
            $pageTemplates = offbeat('page')->getPageTemplates();

            if (is_array($pageTemplates)) {
                $postTemplates = array_merge($postTemplates, $pageTemplates);
            }

            return $postTemplates;
        });
    }

    /** @param mixed[] $data */
    public function applyPageTemplate(string $template, array $data): string
    {
        if (is_singular('page') && empty($data['ignore_page_template'])) {
            $pageTemplate = get_post_meta(get_the_ID(), '_wp_page_template', true);
            if ($pageTemplate && $pageTemplate !== 'default') {
                return $pageTemplate;
            }
        }

        return $template;
    }
}
