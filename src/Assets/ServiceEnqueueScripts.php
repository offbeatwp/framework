<?php
namespace OffbeatWP\Assets;

use OffbeatWP\Services\AbstractService;

class ServiceEnqueueScripts extends AbstractService
{
    public function register()
    {
        if (is_admin()) {
            return;
        }

        add_action('wp_enqueue_scripts',    [$this, 'enqueueScripts'], 1);
        add_filter('script_loader_src',     [$this, 'scriptLoaderSrc'], 10, 2);
    }

    public function enqueueScripts()
    {
        wp_enqueue_script('jquery');

        wp_deregister_script('wp-embed');

        wp_enqueue_style('theme-style', assetUrl('main.css'), [], false, false);

        wp_scripts()->add_data('jquery', 'group', 1);
        wp_scripts()->add_data('jquery-core', 'group', 1);
        wp_scripts()->add_data('jquery-migrate', 'group', 1);

        wp_scripts()->add_data('gform_gravityforms', 'group', 1);
        wp_scripts()->add_data('gform_json', 'group', 1);
        wp_scripts()->add_data('gform_textarea_counter', 'group', 1);

        wp_scripts()->add_data('debug-bar-js', 'group', 1);

        wp_localize_script(
            'jquery-core', 
            'wp_js',
            apply_filters('wp_js_vars', ['ajax_url' => admin_url('admin-ajax.php')])
        );
    }

    public function scriptLoaderSrc($src, $handle)
    {
        switch ($handle) {
            case 'jquery-core':
                $src = assetUrl('main.js');
                break;
            case 'jquery-migrate':
                $src = false;
                break;
        }

        return $src;
    }
}