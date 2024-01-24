<?php
namespace OffbeatWP\Assets;

use OffbeatWP\Services\AbstractService;

class ServiceEnqueueScripts extends AbstractService
{
    /** @return void */
    public function register()
    {
        if (is_admin()) {
            return;
        }

        add_action('wp_enqueue_scripts',    [$this, 'enqueueScripts'], 1);
        add_action('wp_footer',    [$this, 'footerVars'], 5);
    }

    /** @return void */
    public function enqueueScripts()
    {
        if (is_admin() || apply_filters('offbeatwp/assets/include_jquery_by_default', true)) {
            wp_enqueue_script('jquery');
        }

        wp_deregister_script('wp-embed');

        if (apply_filters('offbeatwp/assets/include_main_script_by_default', true)) {
            offbeat('assets')->enqueueScripts('main');
        }

        if (apply_filters('offbeatwp/assets/include_main_style_by_default', true)) {
            offbeat('assets')->enqueueStyles('main');
        }

        if (apply_filters('offbeatwp/scripts/move_to_footer', true)) {
            wp_scripts()->add_data('jquery', 'group', 1);
            wp_scripts()->add_data('jquery-core', 'group', 1);
            wp_scripts()->add_data('jquery-migrate', 'group', 1);

            wp_scripts()->add_data('gform_gravityforms', 'group', 1);
            wp_scripts()->add_data('gform_json', 'group', 1);
            wp_scripts()->add_data('gform_textarea_counter', 'group', 1);

            wp_scripts()->add_data('debug-bar-js', 'group', 1);
        }
    }

    /** @return void */
    public function footerVars()
    {
        $vars = apply_filters('wp_js_vars', ['ajax_url' => admin_url('admin-ajax.php')]);

        if (!$vars) {
            return;
        }

        echo "<script type='text/javascript'>
            /* <![CDATA[ */
                var wp_js = " . json_encode($vars) . ";
            /* ]]> */
        </script>
        ";
    }
}
