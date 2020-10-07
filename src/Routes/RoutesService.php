<?php
namespace OffbeatWP\Routes;

use OffbeatWP\Services\AbstractService;

class RoutesService extends AbstractService
{
    public $bindings = [
        'routes' => RoutesManager::class,
    ];

    public function register()
    {
        add_action('init', [$this, 'loadRoutes'], 10);
        
        if (!is_admin()) {
            add_action('init', [$this, 'urlRoutePreps'], 15);
        }
    }

    public function loadRoutes()
    {
        $routeFiles = glob($this->app->routesPath() . '/*.php');

        foreach ($routeFiles as $routeFile) {
            require $routeFile;
        }
    }

    public function urlRoutePreps()
    {
        if (!offbeat('routes')->findUrlMatch() && !offbeat('routes')->findMatch(true, true)) {
            return null;
        }

        add_filter('user_trailingslashit', function ($url) {
            $urlUnTrailingSlashed = untrailingslashit($url);
            
            if (preg_match('/\.json$/', $urlUnTrailingSlashed)) {
                return $urlUnTrailingSlashed;
            }
            
            return $url;
        }, 20, 1);

        add_action('parse_query', function ($query) {
            if ($query->is_main_query()) {
                $query->is_page = false;
            }
        });

        add_filter('do_parse_request', function ($doParseQuery, $wp) {
            $wp->query_vars = [];
            return false;
        }, 10, 2);

        add_filter('posts_pre_query', function ($posts, \WP_Query $q) {
            if ($q->is_home() && $q->is_main_query()) {
                $posts          = [];
                $q->found_posts = 0;
            }
            return $posts;
        }, 10, 2);

        add_action('pre_handle_404', function ($preHandle404, $query) {
            global $wp_the_query;

            $wp_the_query->is_singular = false;
            $wp_the_query->is_home     = false;

            return true;
        }, 10, 2);
    }
}
