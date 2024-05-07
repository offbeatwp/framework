<?php
namespace OffbeatWP\Assets;

use OffbeatWP\Services\AbstractService;

final class ServiceEnqueueScripts extends AbstractService
{
    public function register(): void
    {
        if (is_admin()) {
            return;
        }

        add_action('wp_enqueue_scripts',    [$this, 'enqueueScripts'], 1);
        add_action('wp_footer',    [$this, 'footerVars'], 5);
    }

    public function enqueueScripts(): void
    {
        wp_deregister_script('wp-embed');

        if (apply_filters('offbeatwp/assets/include_main_script_by_default', true)) {
            offbeat(AssetsManager::class)->enqueueStyles('main');
            offbeat(AssetsManager::class)->enqueueScripts('main');
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

    public function footerVars(): void
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
