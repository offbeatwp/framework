<?php

namespace OffbeatWP\Services;

use OffbeatWP\Contracts\SiteSettings;

class ServiceDisableComments extends AbstractService
{
    public function register()
    {
        if (!is_admin()) return null;

        add_action('admin_menu', function () {
            remove_menu_page( 'edit-comments.php' );
        });

        add_action('wp_before_admin_bar_render', function () {
            global $wp_admin_bar;
            $wp_admin_bar->remove_menu('comments');
        } );

        add_action('admin_init', function () {
            $post_types = get_post_types();

            foreach ($post_types as $post_type) {
                if(post_type_supports($post_type,'comments')) {
                    remove_post_type_support($post_type,'comments');
                    remove_post_type_support($post_type,'trackbacks');
                }
            }
        });
    }
}