<?php

namespace OffbeatWP\SiteSettings;

use OffbeatWP\Contracts\SiteSettings;

abstract class AbstractSiteSettings implements SiteSettings
{
    public function __construct()
    {
        $this->register();
    }
}