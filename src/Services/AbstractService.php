<?php
namespace OffbeatWP\Services;

use OffbeatWP\Contracts\SiteSettings;
use OffbeatWP\Foundation\App;

abstract class AbstractService {
    protected readonly App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    abstract public function register(SiteSettings $settings): void;
}