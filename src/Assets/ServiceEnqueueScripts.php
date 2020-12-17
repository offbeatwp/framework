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
        add_action('wp_footer',    [$this, 'footerVars'], 5);
    }

    public function enqueueScripts()
    {
        wp_enqueue_script('jquery');

        wp_deregister_script('wp-embed');

        offbeat('assets')->enqueueStyles('main');
        offbeat('assets')->enqueueScripts('main');

        wp_scripts()->add_data('jquery', 'group', 1);
        wp_scripts()->add_data('jquery-core', 'group', 1);
        wp_scripts()->add_data('jquery-migrate', 'group', 1);

        wp_scripts()->add_data('gform_gravityforms', 'group', 1);
        wp_scripts()->add_data('gform_json', 'group', 1);
        wp_scripts()->add_data('gform_textarea_counter', 'group', 1);

        wp_scripts()->add_data('debug-bar-js', 'group', 1);
    }

    public function footerVars()
    {
        $vars = apply_filters('wp_js_vars', ['ajax_url' => admin_url('admin-ajax.php')]);

        if (empty($vars)) return;

        echo "<script type='text/javascript'>
            /* <![CDATA[ */
                var wp_js = " . json_encode($vars) . ";
            /* ]]> */
        </script>
        ";
    }
}
