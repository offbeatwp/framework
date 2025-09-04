<?php

namespace OffbeatWP\Wordpress;

use Illuminate\Support\Collection;
use OffbeatWP\Content\Enqueue\WpScriptEnqueueBuilder;
use OffbeatWP\Content\Enqueue\WpStyleEnqueueBuilder;
use OffbeatWP\Support\Wordpress\AdminPage;
use OffbeatWP\Support\Wordpress\Ajax;
use OffbeatWP\Support\Wordpress\Console;
use OffbeatWP\Support\Wordpress\Design;
use OffbeatWP\Support\Wordpress\Hooks;
use OffbeatWP\Support\Wordpress\Page;
use OffbeatWP\Support\Wordpress\Post;
use OffbeatWP\Support\Wordpress\PostType;
use OffbeatWP\Support\Wordpress\RestApi;
use OffbeatWP\Support\Wordpress\Taxonomy;

final class WordpressService
{
    /** @var class-string[] */
    public array $bindings = [
        'admin-page'        => AdminPage::class,
        'ajax'              => Ajax::class,
        'rest-api'          => RestApi::class,
        'console'           => Console::class,
        'hooks'             => Hooks::class,
        'post-type'         => PostType::class,
        'post'              => Post::class,
        'page'              => Page::class,
        'taxonomy'          => Taxonomy::class,
        'design'            => Design::class,
        'enqueue-script'    => WpScriptEnqueueBuilder::class,
        'enqueue-style'     => WpStyleEnqueueBuilder::class,
    ];

    public function register(): void
    {
        add_action('after_setup_theme', [$this, 'registerMenus']);
        add_action('after_setup_theme', [$this, 'registerImageSizes']);
        add_action('widgets_init', [$this, 'registerSidebars']);

        // Page Template
        add_action('theme_page_templates', [$this, 'registerPageTemplate'], 99);
        add_filter('offbeatwp/controller/template', [$this, 'applyPageTemplate'], 10, 2);
    }

    public function registerMenus(): void
    {
        $menus = config('menus');

        if ($menus instanceof Collection && $menus->isNotEmpty()) {
            register_nav_menus($menus->toArray());
        }
    }

    public function registerImageSizes(): void
    {
        $images = config('images');

        if (is_iterable($images)) {
            foreach ($images as $key => $image) {
                add_image_size($key, $image['width'], $image['height'], $image['crop']);
            }
        }
    }

    public function registerSidebars(): void
    {
        $sidebars = config('sidebars');

        if (is_iterable($sidebars)) {
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

    /**
     * @param string $template
     * @param mixed[] $data
     * @return string
     */
    public function applyPageTemplate($template, $data)
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
