<?php
namespace OffbeatWP\Services\HttpStatusPages;

use OffbeatWP\Services\AbstractService;
use OffbeatWP\Contracts\SiteSettings;

class HttpStatusPagesService extends AbstractService
{
    protected $settings;

    public function register(SiteSettings $settings)
    {
        $this->settings = $settings;

        $settings->addPage(HttpStatusPagesSettings::class);
    }

}