<?php

namespace OffbeatWP\Services;

use OffbeatWP\Contracts\SiteSettings;
use OffbeatWP\SiteSettings\SettingsScripts;

class ServiceScripts extends AbstractService
{
    protected $settings;

    public function register(SiteSettings $settings)
    {
        $this->settings = $settings;

        add_action('wp_head',       [$this, 'scriptsHead']);
        add_action('body_open',     [$this, 'scriptsBodyOpen']);
        add_action('wp_footer',     [$this, 'scriptsFooter']);

        $settings->addPage(SettingsScripts::class);
    }

    public function scriptsHead()
    {
        echo $this->settings->get('scripts_head');
    }

    public function scriptsBodyOpen()
    {
        echo $this->settings->get('scripts_open_body');
    }

    public function scriptsFooter()
    {
        echo $this->settings->get('scripts_footer');
    }
}