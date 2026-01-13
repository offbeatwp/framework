<?php

namespace OffbeatWP\Services;

use OffbeatWP\Foundation\WpGlobals;

class ServiceDisableComments extends AbstractService
{
    public function register(): void
    {
        add_action('wp_before_admin_bar_render', function () {
            if (is_admin_bar_showing() && wp_count_comments()->total_comments === 0) {
                WpGlobals::wpAdminBar()?->remove_menu('comments');
            }
        });

        if (!is_admin()) {
            return;
        }

        add_action('admin_menu', static function () {
            remove_menu_page('edit-comments.php');
        });

        add_action('admin_init', static function () {
            foreach (get_post_types() as $postType) {
                if (post_type_supports($postType, 'comments')) {
                    remove_post_type_support($postType, 'comments');
                    remove_post_type_support($postType, 'trackbacks');
                }
            }
        });
    }
}
