<?php
namespace OffbeatWP\Routes;

use OffbeatWP\Services\AbstractService;
use WP_Query;

class RoutesService extends AbstractService
{
    public $bindings = [
        'routes' => RoutesManager::class,
    ];

    public function register()
    {
        $this->loadRoutes();

        if (!is_admin()) {
            add_action('init', [$this, 'urlRoutePreps'], PHP_INT_MAX);
        }
    }

    public function loadRoutes()
    {
        offbeat('routes')->setPriorityMode(RoutesManager::PRIORITY_LOW);

        $routeFiles = glob($this->app->routesPath() . '/*.php');

        foreach ($routeFiles as $routeFile) {
            require $routeFile;
        }

        offbeat('routes')->setPriorityMode(RoutesManager::PRIORITY_HIGH);
    }

    public function urlRoutePreps()
    {
        if (!offbeat('routes')->findRoute()) {
            return;
        }

        add_filter('user_trailingslashit', static function ($url) {
            $urlUnTrailingSlashed = untrailingslashit($url);

            if (preg_match('/\.json$/', $urlUnTrailingSlashed)) {
                return $urlUnTrailingSlashed;
            }

            return $url;
        }, 20, 1);

        add_action('parse_query', static function ($query) {
            if ($query->is_main_query()) {
                $query->is_page = false;
            }
        });

        add_filter('do_parse_request', static function ($doParseQuery, $wp) {
            $wp->query_vars = [];
            return false;
        }, 10, 2);

        add_filter('posts_pre_query', static function ($posts, WP_Query $q) {
            if ($q->is_home() && $q->is_main_query()) {
                $posts = [];
                $q->found_posts = 0;
            }
            return $posts;
        }, 10, 2);

        add_filter('pre_handle_404', static function ($preHandle404, $query) {
            global $wp_the_query;

            $wp_the_query->is_singular = false;
            $wp_the_query->is_home = false;

            return true;
        }, 10, 2);
    }
}
