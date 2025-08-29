<?php

namespace OffbeatWP\Modules;

use OffbeatWP\Foundation\App;
use OffbeatWP\Services\AbstractService;

abstract class AbstractModule extends AbstractService
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }
}
