<?php

namespace OffbeatWP\Services;

use OffbeatWP\Contracts\SiteSettings;
use OffbeatWP\SiteSettings\SettingsScripts;

class ServiceScripts extends AbstractService
{
    /** @var SiteSettings|null */
    protected $settings;

    /** @return void */
    public function register(SiteSettings $settings)
    {
        $this->settings = $settings;

        add_action('wp_head', [$this, 'scriptsHead']);
        add_action('body_open', [$this, 'scriptsBodyOpen']);
        add_action('wp_footer', [$this, 'scriptsFooter']);

        $settings->addPage(SettingsScripts::class);
    }

    /** @return void */
    public function scriptsHead()
    {
        echo $this->settings->get('scripts_head');
    }

    /** @return void */
    public function scriptsBodyOpen()
    {
        echo $this->settings->get('scripts_open_body');
    }

    /** @return void */
    public function scriptsFooter()
    {
        echo $this->settings->get('scripts_footer');
    }
}
