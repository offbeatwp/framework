<?php

namespace OffbeatWP\Services;

use OffbeatWP\Foundation\App;

abstract class AbstractService
{
    protected readonly App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }
}
