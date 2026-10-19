<?php

namespace OffbeatWP\Services;

class ServiceDisableComments extends AbstractService
{
    public function register(): void
    {
        // Close comments and pings on the front end
        add_filter('comments_open', '__return_false', 20);
        add_filter('pings_open', '__return_false', 20);

        // Remove comment/trackback support from all post types
        add_action('init', function (): void {
            foreach (get_post_types() as $postType) {
                if (post_type_supports($postType, 'comments')) {
                    remove_post_type_support($postType, 'comments');
                    remove_post_type_support($postType, 'trackbacks');
                }
            }
        }, 100);

        // Remove admin menu items and redirect direct access
        add_action('admin_menu', function (): void {
            remove_menu_page('edit-comments.php');
            remove_submenu_page('options-general.php', 'options-discussion.php');
        });

        // Remove dashboard widget
        add_action('admin_init', function (): void {
            remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
        });

        // Remove from the admin bar
        add_action('wp_before_admin_bar_render', function (): void {
            if (!wp_count_comments()->total_comments) {
                global $wp_admin_bar;
                $wp_admin_bar->remove_menu('comments');
            }
        });
        add_action('admin_bar_menu', function (WP_Admin_Bar $wpAdminBar): void {
            if (!wp_count_comments()->total_comments) {
                $wpAdminBar->remove_node('comments');
            }
        }, 999);

        // Remove comment feeds and header links
        add_filter('feed_links_show_comments_feed', '__return_false');
        remove_action('wp_head', 'feed_links_extra', 3);

        // Disable the comments REST API endpoints
        add_filter('rest_endpoints', function (array $endpoints): array {
            unset($endpoints['/wp/v2/comments']);
            unset($endpoints['/wp/v2/comments/(?P<id>[\d]+)']);
            return $endpoints;
        });

        // Block direct POSTs to wp-comments-post.php
        add_action('pre_comment_on_post', function (): void {
            wp_die('Comments are disabled.', '', ['response' => 403]);
        });

        // Remove the comment-reply script and the "Recent Comments" widget
        add_action('wp_enqueue_scripts', function (): void {
            wp_dequeue_script('comment-reply');
        }, 100);
        add_action('widgets_init', function (): void {
            unregister_widget('WP_Widget_Recent_Comments');
        });

        // Remove inline CSS added by the Recent Comments widget
        add_action('widgets_init', function (): void {
            global $wp_widget_factory;
            if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
                remove_action('wp_head', [$wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style']);
            }
        });
    }
}
