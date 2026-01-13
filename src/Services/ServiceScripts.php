<?php

namespace OffbeatWP\Services;

final class ServiceScripts extends AbstractService
{
    public function register(): void
    {
        add_action('wp_head', [$this, 'scriptsHead']);
        add_action('wp_footer', [$this, 'scriptsFooter']);
    }

    public function scriptsHead(): void
    {
        echo owp_get_option_string('options_scripts_head');
    }

    public function scriptsFooter(): void
    {
        echo owp_get_option_string('scripts_footer');
    }
}
